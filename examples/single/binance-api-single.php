<?php // https://github.com/jaggedsoft/php-binance-api
// Old version intended for use without composer. Supports PHP 5.6
// This project is no longer maintained by me. I am only maintaining the future branch which requires composer, and the node binance api.
// Credit for updates goes to David Jones: https://github.com/dxjones

class Binance {
	public $btc_value = 0.00;
 	protected $base = "https://api.binance.com/api/", $api_key, $api_secret;
	public function __construct($api_key, $api_secret) {
		$this->api_key = $api_key;
		$this->api_secret = $api_secret;
	}
	public function ping() {
		return $this->request("v1/ping");
	}
	public function time() {
		return $this->request("v1/time");
	}
	public function exchangeInfo() {
		return $this->request("v1/exchangeInfo");
	}
	public function buy_test($symbol, $quantity, $price, $type = "LIMIT", $flags = []) {
		return $this->order_test("BUY", $symbol, $quantity, $price, $type, $flags);
	}
	public function sell_test($symbol, $quantity, $price, $type = "LIMIT", $flags = []) {
		return $this->order_test("SELL", $symbol, $quantity, $price, $type, $flags);
	}
	public function buy($symbol, $quantity, $price, $type = "LIMIT", $flags = []) {
		return $this->order("BUY", $symbol, $quantity, $price, $type, $flags);
	}
	public function sell($symbol, $quantity, $price, $type = "LIMIT", $flags = []) {
		return $this->order("SELL", $symbol, $quantity, $price, $type, $flags);
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
	public function prevDay($symbol) {
		return $this->request("v1/ticker/24hr", ["symbol"=>$symbol]);
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
		$params['timestamp'] = number_format(microtime(true)*1000,0,'.','');
		$query = http_build_query($params, '', '&');
		$signature = hash_hmac('sha256', $query, $this->api_secret);
		$opt = [
			"http" => [
				"method" => $method,
				"ignore_errors" => true,
				"header" => "User-Agent: Mozilla/4.0 (compatible; PHP Binance API)\r\nX-MBX-APIKEY: {$this->api_key}\r\nContent-type: application/x-www-form-urlencoded\r\n"
			]
		];
		if ( $method == 'GET' ) {
			// parameters encoded as query string in URL
			$endpoint = "{$this->base}{$url}?{$query}&signature={$signature}";
		} else {
			// parameters encoded as POST data (in $context)
			$endpoint = "{$this->base}{$url}";
			$postdata = "{$query}&signature={$signature}";
			$opt['http']['content'] = $postdata;
		}
		$context = stream_context_create($opt);
		return json_decode(file_get_contents($endpoint, false, $context), true);
	}
	private function order_test($side, $symbol, $quantity, $price, $type = "LIMIT", $flags = []) {
		$opt = [
			"symbol" => $symbol,
			"side" => $side,
			"type" => $type,
			"quantity" => $quantity,
			"recvWindow" => 60000
		];
		if ( $type == "LIMIT" ) {
			$opt["price"] = $price;
			$opt["timeInForce"] = "GTC";
		}
		// allow additional options passed through $flags
		if ( isset($flags['recvWindow']) ) $opt['recvWindow'] = $flags['recvWindow'];
		if ( isset($flags['timeInForce']) ) $opt['timeInForce'] = $flags['timeInForce'];
		if ( isset($flags['stopPrice']) ) $opt['stopPrice'] = $flags['stopPrice'];
		if ( isset($flags['icebergQty']) ) $opt['icebergQty'] = $flags['icebergQty'];
		return $this->signedRequest("v3/order/test", $opt, "POST");
	}
	private function order($side, $symbol, $quantity, $price, $type = "LIMIT", $flags = []) {
		$opt = [
			"symbol" => $symbol,
			"side" => $side,
			"type" => $type,
			"quantity" => $quantity,
			"recvWindow" => 60000
		];
		if ( $type == "LIMIT" ) {
			$opt["price"] = $price;
			$opt["timeInForce"] = "GTC";
		}
		// allow additional options passed through $flags
		if ( isset($flags['recvWindow']) ) $opt["recvWindow"] = $flags['recvWindow'];
		if ( isset($flags['timeInForce']) ) $opt["timeInForce"] = $flags['timeInForce'];
		if ( isset($flags['stopPrice']) ) $opt['stopPrice'] = $flags['stopPrice'];
		if ( isset($flags['icebergQty']) ) $opt['icebergQty'] = $flags['icebergQty'];
		return $this->signedRequest("v3/order", $opt, "POST");
	}
	//1m,3m,5m,15m,30m,1h,2h,4h,6h,8h,12h,1d,3d,1w,1M
	public function candlesticks($symbol, $interval = "5m") {
		return $this->request("v1/klines",["symbol"=>$symbol, "interval"=>$interval]);
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
