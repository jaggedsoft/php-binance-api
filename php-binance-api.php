<?php
/* ============================================================
 * php-binance-api
 * https://github.com/jaggedsoft/php-binance-api
 * ============================================================
 * Copyright 2017-, Jon Eyrick
 * Released under the MIT License
 * ============================================================ */

require __DIR__.'/vendor/autoload.php';
class binance {
	public $btc_value = 0.00;
	protected $base = "https://www.binance.com/api/", $api_key, $api_secret;
	protected $depthCache = [], $depthQueue = [], $info = [];
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
	public function prevDay($symbol) {
		return $this->request("v1/24hr",["symbol"=>$symbol]);
	}
	public function depth($symbol) {
		$json = $this->request("v1/depth",["symbol"=>$symbol]);
		if ( !isset($this->info[$symbol]) ) $this->info[$symbol] = [];
		$this->info[$symbol]['firstUpdate'] = $json['lastUpdateId'];
		return $this->depthData($symbol, $json);
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
	private function depthHandler($json) {
		$symbol = $json['s'];
		if ( $json['u'] <= $this->info[$symbol]['firstUpdate'] ) return;
		foreach ( $json['b'] as $bid ) {
			$this->depthCache[$symbol]['bids'][$bid[0]] = $bid[1];
			if ( $bid[1] == "0.00000000" ) unset($this->depthCache[$symbol]['bids'][$bid[0]]);
		}
		foreach ( $json['a'] as $ask ) {
			$this->depthCache[$symbol]['asks'][$ask[0]] = $ask[1];
			if ( $ask[1] == "0.00000000" ) unset($this->depthCache[$symbol]['asks'][$ask[0]]);
		}
	}
	public function first($array) {
		return array_keys($array)[0];
	}
	public function last($array) {
		return array_keys(array_slice($array, -1))[0];
	}
	public function displayDepth($array) {
		foreach ( ['asks', 'bids'] as $type ) {
			$entries = $array[$type];
			if ( $type == 'asks' ) $entries = array_reverse($entries);
			echo "{$type}:".PHP_EOL;
			foreach ( $entries as $price => $quantity ) {
				$total = number_format($price * $quantity,8,'.','');
				$quantity = str_pad(str_pad(number_format(rtrim($quantity,'.0')),10,' ',STR_PAD_LEFT),15);
				echo "{$price} {$quantity} {$total}".PHP_EOL;
			}
			//echo str_repeat('-', 32).PHP_EOL;
		}
	}
	public function sortDepth($symbol, $limit = 11) {
		$bids = $this->depthCache[$symbol]['bids'];
		$asks = $this->depthCache[$symbol]['asks'];
		krsort($bids);
		ksort($asks);
		return ["asks"=> array_slice($asks, 0, $limit, true), "bids"=> array_slice($bids, 0, $limit, true)];
	}
	private function depthData($symbol, $json) {
		$bids = $asks = [];
		foreach ( $json['bids'] as $obj ) {
			$bids[$obj[0]] = $obj[1];
		}
		foreach ( $json['asks'] as $obj ) {
			$asks[$obj[0]] = $obj[1];
		}
		return $this->depthCache[$symbol] = ["bids"=>$bids, "asks"=>$asks];
	}
	////////////////////////////////////
	// WebSockets
	public function depthCache($symbols, $callback) {
		if ( !is_array($symbols) ) $symbols = [$symbols];
		foreach ( $symbols as $symbol ) {
			if ( !isset($this->info[$symbol]) ) $this->info[$symbol] = ['depthCallback' => $callback];
			if ( !isset($this->depthQueue[$symbol]) ) $this->depthQueue[$symbol] = [];
			if ( !isset($this->depthCache[$symbol]) ) $this->depthCache[$symbol] = ["bids" => [], "asks" => []];
			$this->info[$symbol]['firstUpdate'] = 0;
			\Ratchet\Client\connect('wss://stream.binance.com:9443/ws/'.strtolower($symbol).'@depth')->then(function($ws) {
				$ws->on('message', function($data) use($ws) {
					$json = json_decode($data, true);
					$symbol = $json['s'];
					if ( $this->info[$symbol]['firstUpdate'] == 0 ) {
						$this->depthQueue[$symbol][] = $json;
						return;
					}
					$this->depthHandler($json);
					$this->info[$symbol]['depthCallback']($this, $symbol, $this->depthCache[$symbol]);
				});
				$ws->on('close', function($code = null, $reason = null) {
					echo "depthCache({$symbol}) WebSocket Connection closed ({$code} - {$reason})\n";
				});
			}, function($e) {
				echo "depthCache({$symbol})) Could not connect: {$e->getMessage()}\n";
			});
			$this->depth($symbol);
			foreach ( $this->depthQueue[$symbol] as $data ) {
				$this->depthHandler($json);
			}
			$this->depthQueue[$symbol] = [];
			$callback($this, $symbol, $this->depthCache[$symbol]);
		}
	}
	public function userData() {
		$loop = React\EventLoop\Factory::create();
		$loop->addPeriodicTimer(1, function() {
			echo "tick\n";
		});
		$loop->run();
	}
}
