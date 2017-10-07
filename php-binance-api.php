<?php // https://github.com/jaggedsoft/php-binance-api

class Binance {
	public $btc_value = 0.00;
	protected $base = "https://www.binance.com/api/", $api_key, $api_secret;
	public function __construct($api_key, $api_secret) {
		$this->api_key = $api_key;
		$this->api_secret = $api_secret;
	}
	public function buy($symbol, $quantity, $price, $type = "LIMIT") {
		return $this->order("BUY", $symbol, $quantity, $price, $type);
	}
	public function sell($symbol, $quantity, $price, $type = "LIMIT") {
		return $this->order("SELL", $symbol, $quantity, $price, $type);
	}
	public function cancel($symbol, $orderid) {
		return $this->signedRequest("v3/order",["symbol"=>$symbol, "orderId"=>$orderid], "DELETE");
	}
	public function orderStatus($symbol, $orderid) {
		return $this->signedRequest("v3/order",["symbol"=>$symbol, "orderId"=>$orderid]);
	}
	public function openOrders($symbol) {
		return $this->signedRequest("v3/openOrders",["symbol"=>$symbol]);
	}
	public function orders($symbol, $limit = 500) {
		return $this->signedRequest("v3/allOrders",["symbol"=>$symbol, "limit"=>$limit]);
	}
	public function trades($symbol) {
		return $this->signedRequest("v3/myTrades",["symbol"=>$symbol]);
	}
	public function prices() {
		return $this->priceData($this->request("v1/ticker/allPrices"));
	}
	public function bookPrices() {
		return $this->bookPriceData($this->request("v1/ticker/allBookTickers"));
	}
	public function account() {
		return $this->signedRequest("v3/account");
	}
	public function depth($symbol) {
		return $this->request("v1/depth",["symbol"=>$symbol]);
	}
	public function balances($priceData = false) {
		return $this->balanceData($this->signedRequest("v3/account"),$priceData);
	}
	private function request($url, $params = [], $method = "GET") {
		$opt = [
			"http" => [
				"method" => $method,
				"header" => "User-Agent: Mozilla/4.0 (compatible; PHP Binance API)\r\n"
			]
		];
		$context = stream_context_create($opt);
		$query = http_build_query($params, '', '&');
		return json_decode(file_get_contents($this->base.$url.'?'.$query, false, $context), true);
	}
	private function signedRequest($url, $params = [], $method = "GET") {
		$opt = [
			"http" => [
				"method" => $method,
				"header" => "User-Agent: Mozilla/4.0 (compatible; PHP Binance API)\r\nX-MBX-APIKEY: {$this->api_key}\r\n"
			]
		];
		$context = stream_context_create($opt);
		$params['timestamp'] = number_format(microtime(true)*1000,0,'.','');
		$query = http_build_query($params, '', '&');
		$signature = hash_hmac('sha256', $query, $this->api_secret);
		$endpoint = "{$this->base}{$url}?{$query}&signature={$signature}";
		return json_decode(file_get_contents($endpoint, false, $context), true);
	}
	private function order($side, $symbol, $quantity, $price, $type = "LIMIT") {
		$opt = [
			"symbol" => $symbol,
			"side" => $side,
			"type" => $type,
			"price" => $price,
			"quantity" => $quantity,
			"timeInForce" => "GTC",
			"recvWindow" => 60000
		];
		return $this->signedRequest("v3/order", $opt, "POST");
	}
	private function balanceData($array, $priceData = false) {
		if ( $priceData ) $btc_value = 0.00;
		$balances = [];
		foreach ( $array['balances'] as $obj ) {
			$asset = $obj['asset'];
			$balances[$asset] = ["available"=>$obj['free'], "onOrder"=>$obj['locked'], "btcValue"=>0.00000000];
			if ( $priceData ) {
				if ( $obj['free'] < 0.00000001 ) continue;
				if ( $asset == 'BTC' ) {
					$balances[$asset]['btcValue'] = $obj['free'];
					$btc_value+= $obj['free'];
					continue;
				}
				$btcValue = number_format($obj['free'] * $priceData[$asset.'BTC'],8,'.','');
				$balances[$asset]['btcValue'] = $btcValue;
				$btc_value+= $btcValue;
			}
		}
		if ( $priceData ) {
			uasort($balances, function($a, $b) { return $a['btcValue'] < $b['btcValue']; });
			$this->btc_value = $btc_value;
		}
		return $balances;
	}
	private function bookPriceData($array) {
		$bookprices = [];
		foreach ( $array as $obj ) {
			$bookprices[$obj['symbol']] = [
				"bid"=>$obj['bidPrice'],
				"bids"=>$obj['bidQty'],
				"ask"=>$obj['askPrice'],
				"asks"=>$obj['askQty']
			];
		}
		return $bookprices;
	}
	private function priceData($array) {
		$prices = [];
		foreach ( $array as $obj ) {
			$prices[$obj['symbol']] = $obj['price'];
		}
		return $prices;
	}
}

// https://www.binance.com/restapipub.html
