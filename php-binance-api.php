<?php
/* ============================================================
 * php-binance-api
 * https://github.com/jaggedsoft/php-binance-api
 * ============================================================
 * Copyright 2017-, Jon Eyrick
 * Released under the MIT License
 * ============================================================ */
namespace Binance;
class API {
	protected $base = "https://www.binance.com/api/", $api_key, $api_secret;
	protected $depthCache = [], $depthQueue = [];
	protected $charts = [], $chartQueue = [];
	protected $info = [];
	public $balances = [], $btc_value = 0.00;
	public function __construct($api_key = '', $api_secret = '') {
		$this->api_key = $api_key;
		$this->api_secret = $api_secret;
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
	public function history($symbol) {
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
	public function aggTrades($symbol) {
		return $this->tradesData($this->request("v1/aggTrades",["symbol"=>$symbol]));
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
	
	private function apiRequest($url, $method = "GET") {
		$opt = [
			"http" => [
				"method" => $method,
				"header" => "User-Agent: Mozilla/4.0 (compatible; PHP Binance API)\r\nX-MBX-APIKEY: {$this->api_key}\r\n"
			]
		];
		$context = stream_context_create($opt);
		return json_decode(file_get_contents($this->base.$url, false, $context), true);
	}
	
	public function order($side, $symbol, $quantity, $price, $type = "LIMIT", $flags = []) {
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
		if ( isset($flags['stopPrice']) ) $opt['stopPrice'] = $flags['stopPrice'];
		if ( isset($flags['icebergQty']) ) $opt['icebergQty'] = $flags['icebergQty'];
		return $this->signedRequest("v3/order", $opt, "POST");
	}
	
	//1m,3m,5m,15m,30m,1h,2h,4h,6h,8h,12h,1d,3d,1w,1M
	public function candlesticks($symbol, $interval = "5m") {
		if ( !isset($this->charts[$symbol]) ) $this->charts[$symbol] = [];
		$response = $this->request("v1/klines", ["symbol"=>$symbol, "interval"=>$interval]);
		$ticks = $this->chartData($symbol, $interval, $response);
		$this->charts[$symbol][$interval] = $ticks;
		return $ticks;
	}
	
	// Converts all your balances into a nice array
	// If priceData is passed from $api->prices() it will add btcValue to each symbol
	// This function sets $btc_value which is your estimated BTC value of all assets combined
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
	
	// Convert kline data into object
	private function chartData($symbol, $interval, $ticks) {
		if ( !isset($this->info[$symbol]) ) $this->info[$symbol] = [];
		if ( !isset($this->info[$symbol][$interval]) ) $this->info[$symbol][$interval] = [];
		$output = [];
		foreach ( $ticks as $tick ) {
			list($openTime, $open, $high, $low, $close, $assetVolume, $closeTime, $baseVolume, $trades, $assetBuyVolume, $takerBuyVolume, $ignored) = $tick;
			$output[$openTime] = [
				"open" => $open,
				"high" => $high,
				"low" => $low,
				"close" => $close,
				"volume" => $baseVolume
			];
		}
		$this->info[$symbol][$interval]['firstOpen'] = $openTime;
		return $output;
	}
	
	// Convert aggTrades data into easier format
	private function tradesData($trades) {
		$output = [];
		foreach ( $trades as $trade ) {
			$price = $trade['p'];
			$quantity = $trade['q'];
			$timestamp = $trade['T'];
			$maker = $trade['m'] ? 'true' : 'false';
			$output[] = ["price"=>$price, "quantity"=> $quantity, "timestamp"=>$timestamp, "maker"=>$maker];
		}
		return $output;
	}
	
	// Consolidates Book Prices into an easy to use object 
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
	
	// Converts Price Data into an easy key/value array
	private function priceData($array) {
		$prices = [];
		foreach ( $array as $obj ) {
			$prices[$obj['symbol']] = $obj['price'];
		}
		return $prices;
	}
	
	// For WebSocket Depth Cache
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
	
	// For WebSocket Chart Cache
	private function chartHandler($symbol, $interval, $json) {
		if ( !$this->info[$symbol][$interval]['firstOpen'] ) { // Wait for /kline to finish loading
			$this->chartQueue[$symbol][$interval][] = $json;
			return;
		}
		$chart = $json->k;
		$symbol = $json->s;
		$interval = $chart->i;
		$tick = $chart->t;
		if ( $tick < $this->info[$symbol][$interval]['firstOpen'] ) return; // Filter out of sync data
		$open = $chart->o;
		$high = $chart->h;
		$low = $chart->l;
		$close = $chart->c;
		$volume = $chart->q; //+trades buyVolume assetVolume makerVolume
		$this->charts[$symbol][$interval][$tick] = ["open"=>$open, "high"=>$high, "low"=>$low, "close"=>$close, "volume"=>$volume];
	}
	
	// Gets first key of an array
	public function first($array) {
		return array_keys($array)[0];
	}
	
	// Gets last key of an array
	public function last($array) {
		return array_keys(array_slice($array, -1))[0];
	}
	
	// Formats nicely for console output
	public function displayDepth($array) {
		$output = '';
		foreach ( ['asks', 'bids'] as $type ) {
			$entries = $array[$type];
			if ( $type == 'asks' ) $entries = array_reverse($entries);
			$output.= "{$type}:".PHP_EOL;
			foreach ( $entries as $price => $quantity ) {
				$total = number_format($price * $quantity,8,'.','');
				$quantity = str_pad(str_pad(number_format(rtrim($quantity,'.0')),10,' ',STR_PAD_LEFT),15);
				$output.= "{$price} {$quantity} {$total}".PHP_EOL;
			}
			//echo str_repeat('-', 32).PHP_EOL;
		}
		return $output;
	}
	
	// Sorts depth data for display & getting highest bid and lowest ask
	public function sortDepth($symbol, $limit = 11) {
		$bids = $this->depthCache[$symbol]['bids'];
		$asks = $this->depthCache[$symbol]['asks'];
		krsort($bids);
		ksort($asks);
		return ["asks"=> array_slice($asks, 0, $limit, true), "bids"=> array_slice($bids, 0, $limit, true)];
	}
	
	// Formats depth data for nice display
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
	////////////////////////////////////
	
	// Pulls /depth data and subscribes to @depth WebSocket endpoint
	// Maintains a local Depth Cache in sync via lastUpdateId. See depth() and depthHandler()
	public function depthCache($symbols, $callback) {
		if ( !is_array($symbols) ) $symbols = [$symbols];
		foreach ( $symbols as $symbol ) {
			if ( !isset($this->info[$symbol]) ) $this->info[$symbol] = [];
			$this->info[$symbol]['depthCallback'] = $callback;
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
					echo "depthCache({$symbol}) WebSocket Connection closed! ({$code} - {$reason})".PHP_EOL;
				});
			}, function($e) {
				echo "depthCache({$symbol})) Could not connect: {$e->getMessage()}".PHP_EOL;
			});
			$this->depth($symbol);
			foreach ( $this->depthQueue[$symbol] as $data ) {
				$this->depthHandler($json);
			}
			$this->depthQueue[$symbol] = [];
			$callback($this, $symbol, $this->depthCache[$symbol]);
		}
	}
	
	// Trades WebSocket Endpointc
	public function trades($symbols, $callback) {
		foreach ( $symbols as $symbol ) {
			if ( !isset($this->info[$symbol]) ) $this->info[$symbol] = [];
			$this->info[$symbol]['tradesCallback'] = $callback;
			\Ratchet\Client\connect('wss://stream.binance.com:9443/ws/'.strtolower($symbol).'@aggTrade')->then(function($ws) {
				$ws->on('message', function($data) use($ws) {
					$json = json_decode($data, true);
					$symbol = $json['s'];
					$price = $json['p'];
					$quantity = $json['q'];
					$timestamp = $json['T'];
					$maker = $json['m'] ? 'true' : 'false';
					$trades = ["price"=>$price, "quantity"=>$quantity, "timestamp"=>$timestamp, "maker"=>$maker];
					$this->info[$symbol]['tradesCallback']($this, $symbol, $trades);
				});
				$ws->on('close', function($code = null, $reason = null) {
					echo "trades({$symbol}) WebSocket Connection closed! ({$code} - {$reason})".PHP_EOL;
				});
			}, function($e) {
				echo "trades({$symbol}) Could not connect: {$e->getMessage()}".PHP_EOL;
			});
		}
	}
	
	
	// Pulls /kline data and subscribes to @klines WebSocket endpoint
	public function chart($symbols, $interval = "30m", $callback) {
		foreach ( $symbols as $symbol ) {
			if ( !isset($this->charts[$symbol]) ) $this->charts[$symbol] = [];
			$this->charts[$symbol][$interval] = [];
			if ( !isset($this->info[$symbol]) ) $this->info[$symbol] = [];
			if ( !isset($this->info[$symbol][$interval]) ) $this->info[$symbol][$interval] = [];
			if ( !isset($this->chartQueue[$symbol]) ) $this->chartQueue[$symbol] = [];
			$this->chartQueue[$symbol][$interval] = [];
			$this->info[$symbol][$interval]['firstOpen'] = 0;
			$this->info[$symbol]['chartCallback'.$interval] = $callback;
			\Ratchet\Client\connect('wss://stream.binance.com:9443/ws/'.strtolower($symbol).'@kline_'.$interval)->then(function($ws) {
				$ws->on('message', function($data) use($ws) {
					$json = json_decode($data);
					$chart = $json->k;
					$symbol = $json->s;
					$interval = $chart->i;
					$this->chartHandler($symbol, $interval, $json);
					$this->info[$symbol]['chartCallback'.$interval]($this, $symbol, $this->charts[$symbol][$interval]);
				});
				$ws->on('close', function($code = null, $reason = null) {
					echo "chart({$symbol},{$interval}) WebSocket Connection closed! ({$code} - {$reason})".PHP_EOL;
				});
			}, function($e) {
				echo "chart({$symbol},{$interval})) Could not connect: {$e->getMessage()}".PHP_EOL;
			});
			$this->candlesticks($symbol, $interval);
			foreach ( $this->chartQueue[$symbol][$interval] as $json ) {
				$this->chartHandler($symbol, $interval, $json);
			}
			$this->chartQueue[$symbol][$interval] = [];
			$this->info[$symbol]['chartCallback'.$interval]($this, $symbol, $this->charts[$symbol][$interval]);
		}
	}
	
	// Issues userDataStream token and keepalive, subscribes to userData WebSocket
	public function userData($balance_callback, $execution_callback = false) {
		$response = $this->apiRequest("v1/userDataStream", "POST");
		$listenKey = $this->options['listenKey'] = $response['listenKey'];
		
		$this->info['balanceCallback'] = $balance_callback;
		$this->info['executionCallback'] = $execution_callback;
		\Ratchet\Client\connect('wss://stream.binance.com:9443/ws/'.$listenKey)->then(function($ws) {
			$ws->on('message', function($data) use($ws) {
				$json = json_decode($data);
				$type = $json->e;
				if ( $type == "outboundAccountInfo") {
					$balances = [];
					$this->info['balanceCallback']($this, $balances);
				} elseif ( $type == "executionReport" ) {
					$report = [];
					$this->info['executionCallback']($this, $report);
				}
			});
			$ws->on('close', function($code = null, $reason = null) {
				echo "chart({$symbol},{$interval}) WebSocket Connection closed! ({$code} - {$reason})".PHP_EOL;
			});
		}, function($e) {
			echo "chart({$symbol},{$interval})) Could not connect: {$e->getMessage()}".PHP_EOL;
		});
		// websocket
		//if execution callback
		$loop = \React\EventLoop\Factory::create();
		$loop->addPeriodicTimer(30, function() use (&$listenKey) {
			$this->apiRequest("v1/userDataStream?listenKey={$listenKey}", "PUT");
		});
		$loop->run();
	}
}
