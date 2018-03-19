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
	protected $base = "https://api.binance.com/api/";
	protected $wapi = "https://api.binance.com/wapi/";
	protected $api_key;
	protected $api_secret;
	protected $depthCache = [];
	protected $depthQueue = [];
	protected $chartQueue = [];
	protected $charts = [];
	protected $info = ["timeOffset"=>0];
	protected $proxyConf = null;
	protected $transfered = 0;
	protected $requestCount = 0;
	public $httpDebug = false;
	public $balances = [];
	public $btc_value = 0.00; // value of available assets
	public $btc_total = 0.00; // value of available + onOrder assets
	public function __construct($api_key = '', $api_secret = '', $options = ["useServerTime"=>false], $proxyConf = null) {
		$this->api_key = $api_key;
		$this->api_secret = $api_secret;
		$this->proxyConf = $proxyConf;

		if(isset($options['useServerTime']) && $options['useServerTime']) {
			$this->useServerTime();
		}

		$this->setupApiConfigFromFile();
	}
	private function setupApiConfigFromFile()
	{
		if(empty($this->api_key) == false || empty($this->api_key) == false) {
			return;
		}
		if(file_exists(getenv("HOME") . "/.config/jaggedsoft/php-binance-api.json") == false) {
			return;
		}
		$contents = json_decode(file_get_contents(getenv("HOME") . "/.config/jaggedsoft/php-binance-api.json"), true);
		$this->api_key = isset($contents['api-key']) ? $contents['api-key'] : "";
		$this->api_secret = isset($contents['api-secret']) ? $contents['api-secret'] : "";
	}
	public function buy($symbol, $quantity, $price, $type = "LIMIT", $flags = []) {
		return $this->order("BUY", $symbol, $quantity, $price, $type, $flags);
	}
	public function buyTest($symbol, $quantity, $price, $type = "LIMIT", $flags = []) {
		return $this->orderTest("BUY", $symbol, $quantity, $price, $type, $flags);
	}
	public function sell($symbol, $quantity, $price, $type = "LIMIT", $flags = []) {
		return $this->order("SELL", $symbol, $quantity, $price, $type, $flags);
	}
	public function sellTest($symbol, $quantity, $price, $type = "LIMIT", $flags = []) {
		return $this->orderTest("SELL", $symbol, $quantity, $price, $type, $flags);
	}
	public function marketBuy($symbol, $quantity, $flags = []) {
		return $this->order("BUY", $symbol, $quantity, 0, "MARKET", $flags);
	}
	public function marketBuyTest($symbol, $quantity, $flags = []) {
		return $this->orderTest("BUY", $symbol, $quantity, 0, "MARKET", $flags);
	}
	public function marketSell($symbol, $quantity, $flags = []) {
		return $this->order("SELL", $symbol, $quantity, 0, "MARKET", $flags);
	}
	public function marketSellTest($symbol, $quantity, $flags = []) {
		return $this->orderTest("SELL", $symbol, $quantity, 0, "MARKET", $flags);
	}
	public function cancel($symbol, $orderid) {
		return $this->httpRequest("v3/order", "DELETE", ["symbol"=>$symbol, "orderId"=>$orderid], true);
	}
	public function orderStatus($symbol, $orderid) {
		return $this->httpRequest("v3/order", "GET" ,["symbol"=>$symbol, "orderId"=>$orderid], true);
	}
	public function openOrders($symbol) {
		$params = [];
		if($symbol)
		    $params = ["symbol"=>$symbol];
		return $this->httpRequest("v3/openOrders","GET", $params, true);
	}
	public function orders($symbol, $limit = 500, $fromOrderId = 1) {
		return $this->httpRequest("v3/allOrders", "GET", ["symbol"=>$symbol, "limit"=>$limit, "orderId"=>$fromOrderId], true);
	}
	public function history($symbol, $limit = 500, $fromTradeId = 1) {
		return $this->httpRequest("v3/myTrades", "GET", ["symbol"=>$symbol, "limit"=>$limit, "fromId"=>$fromTradeId], true);
	}
	public function useServerTime() {
		$serverTime = $this->httpRequest("v1/time")['serverTime'];
		$this->info['timeOffset'] = $serverTime - (microtime(true)*1000);
	}
	public function time() {
		return $this->httpRequest("v1/time");
	}
	public function exchangeInfo() {
		return $this->httpRequest("v1/exchangeInfo");
	}
	public function withdraw($asset, $address, $amount, $addressTag = false) {
		$options = ["asset"=>$asset, "address"=>$address, "amount"=>$amount, "wapi"=>true, "name"=>"API Withdraw"];
		if ( $addressTag ) $options['addressTag'] = $addressTag;
		return $this->httpRequest("v3/withdraw.html", "POST", $options, true);
	}
	public function depositAddress($asset) {
		$params = ["wapi"=>true, "asset"=>$asset];
		return $this->httpRequest("v3/depositAddress.html", "GET", $params, true);
	}
	public function depositHistory($asset = false) {
		$params = ["wapi"=>true];
		if ( $asset ) $params['asset'] = $asset;
		return $this->httpRequest("v3/depositHistory.html", "GET", $params, true);
	}
	public function withdrawHistory($asset = false) {
		$params = ["wapi"=>true];
		if ( $asset ) $params['asset'] = $asset;
		return $this->httpRequest("v3/withdrawHistory.html", "GET", $params, true);
	}
	public function prices() {
		return $this->priceData($this->httpRequest("v3/ticker/price"));
	}
	public function bookPrices() {
		return $this->bookPriceData($this->httpRequest("v3/ticker/bookTicker"));
	}
	public function account() {
		return $this->httpRequest("v3/account", "GET", [], true);
	}
	public function prevDay($symbol) {
		return $this->httpRequest("v1/ticker/24hr", "GET", ["symbol"=>$symbol]);
	}
	public function aggTrades($symbol) {
		return $this->tradesData($this->httpRequest("v1/aggTrades", "GET", ["symbol"=>$symbol]));
	}
	public function depth($symbol) {
		$json = $this->httpRequest("v1/depth", "GET", ["symbol"=>$symbol]);
		if(!isset($this->info[$symbol])) $this->info[$symbol] = [];
		$this->info[$symbol]['firstUpdate'] = $json['lastUpdateId'];
		return $this->depthData($symbol, $json);
	}
	public function balances($priceData = false) {
		return $this->balanceData($this->httpRequest("v3/account", "GET", [], true), $priceData);
	}
	public function getProxyUriString()
	{
		$uri = isset( $this->proxyConf['proto'] ) ? $this->proxyConf['proto'] : "http";
		// https://curl.haxx.se/libcurl/c/CURLOPT_PROXY.html
		$supportedProxyProtocols = array('http', 'https', 'socks4', 'socks4a', 'socks5', 'socks5h');
		if(in_array($uri, $supportedProxyProtocols) == false) {
			die("Unknown proxy protocol '" . $this->proxyConf['proto'] . "', supported protocols are " . implode(", ",$supportedProxyProtocols)  . "\n");
		}

		$uri .= "://";
		$uri .= isset( $this->proxyConf['address'] ) ? $this->proxyConf['address'] : "localhost";
		if( isset( $this->proxyConf['address'] ) == false ) echo "warning: proxy address not set defaulting to localhost\n";
		$uri .= ":";
		$uri .= isset( $this->proxyConf['port'] ) ? $this->proxyConf['port'] : "1080";
		if( isset( $this->proxyConf['address'] ) == false ) echo "warning: proxy port not set defaulting to 1080\n";
		return $uri;
	}
	public function setProxy( $proxyconf ) {
		$this->proxyConf = $proxyconf;
	}

	private function httpRequest($url, $method = "GET", $params = [], $signed = false) {
		// is cURL installed yet?
		if (!function_exists('curl_init')) {
			die('Sorry cURL is not installed!');
		}

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_VERBOSE, $this->httpDebug);
		$query = http_build_query($params, '', '&');

		// signed with params
		if($signed == true) {
			if(empty($this->api_key) ) die("signedRequest error: API Key not set!");
			if(empty($this->api_secret) ) die("signedRequest error: API Secret not set!");
			$base = $this->base;
			$ts = (microtime(true)*1000) + $this->info['timeOffset'];
			$params['timestamp'] = number_format($ts,0,'.','');
			if(isset($params['wapi'])) {
				unset($params['wapi']);
				$base = $this->wapi;
			}
			$query = http_build_query($params, '', '&');
			$signature = hash_hmac('sha256', $query, $this->api_secret);
			$endpoint = $base.$url.'?'.$query.'&signature='.$signature;
			curl_setopt($ch, CURLOPT_URL, $endpoint);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-MBX-APIKEY: ' . $this->api_key));
		}
		// params so buildquery string and append to url
		else if(count($params)>0){
			curl_setopt($ch, CURLOPT_URL, $this->base.$url.'?'.$query);
		}
		// no params so just the base url
		else {
			curl_setopt($ch, CURLOPT_URL, $this->base.$url);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-MBX-APIKEY: ' . $this->api_key));
		}

		curl_setopt($ch, CURLOPT_USERAGENT, "User-Agent: Mozilla/4.0 (compatible; PHP Binance API)");

		// Post and postfields
		if($method == "POST") {
			curl_setopt($ch, CURLOPT_POST, true);
			//curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
		}

		// Delete Method
		if($method == "DELETE") {
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
		}

		// proxy settings
		if(is_array($this->proxyConf)) {
			curl_setopt($ch, CURLOPT_PROXY, $this->getProxyUriString());
			if(isset($this->proxyConf['user']) && isset($this->proxyConf['pass'])) {
				curl_setopt($ch, CURLOPT_PROXYUSERPWD, $this->proxyConf['user'] . ':' . $this->proxyConf['pass']);
			}
		}

		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		// headers will proceed the output, json_decode will fail below
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		$output = curl_exec($ch);

		// Check if any error occurred
		if(curl_errno($ch) > 0)	{
			echo 'Curl error: ' . curl_error($ch) . "\n";
			return [];
		}

		curl_close($ch);
		$json = json_decode($output, true);

		if(isset($json['msg'])) {
			echo "signedRequest error: {$output}".PHP_EOL;
		}

		$this->transfered += strlen( $output );
		$this->requestCount++;

		return $json;
	}

	public function order($side, $symbol, $quantity, $price, $type = "LIMIT", $flags = []) {
		$opt = [
			"symbol" => $symbol,
			"side" => $side,
			"type" => $type,
			"quantity" => $quantity,
			"recvWindow" => 60000
		];
		if ( $type === "LIMIT" || $type === "STOP_LOSS_LIMIT" || $type === "TAKE_PROFIT_LIMIT" ) {
			$opt["price"] = $price;
			$opt["timeInForce"] = "GTC";
		}
		if ( isset($flags['stopPrice']) ) $opt['stopPrice'] = $flags['stopPrice'];
		if ( isset($flags['icebergQty']) ) $opt['icebergQty'] = $flags['icebergQty'];
		if ( isset($flags['newOrderRespType']) ) $opt['newOrderRespType'] = $flags['newOrderRespType'];
		return $this->httpRequest("v3/order", "POST", $opt, true);
	}

	public function orderTest($side, $symbol, $quantity, $price, $type = "LIMIT", $flags = []) {
		$opt = [
			"symbol" => $symbol,
			"side" => $side,
			"type" => $type,
			"quantity" => $quantity,
			"recvWindow" => 60000
		];
		if ( $type === "LIMIT" || $type === "STOP_LOSS_LIMIT" || $type === "TAKE_PROFIT_LIMIT" ) {
			$opt["price"] = $price;
			$opt["timeInForce"] = "GTC";
		}
		if ( isset($flags['stopPrice']) ) $opt['stopPrice'] = $flags['stopPrice'];
		if ( isset($flags['icebergQty']) ) $opt['icebergQty'] = $flags['icebergQty'];
		if ( isset($flags['newOrderRespType']) ) $opt['newOrderRespType'] = $flags['newOrderRespType'];
		return $this->httpRequest("v3/order/test", "POST", $opt,true);
	}

	//1m,3m,5m,15m,30m,1h,2h,4h,6h,8h,12h,1d,3d,1w,1M
	public function candlesticks($symbol, $interval = "5m", $limit = null, $startTime= null, $endTime = null) {
		if ( !isset($this->charts[$symbol]) ) $this->charts[$symbol] = [];
		$opt = [
		    "symbol" => $symbol,
		    "interval" => $interval
		];
		if ($limit) $opt["limit"] = $limit;
		if ($startTime) $opt["startTime"] = $startTime;
		if ($endTime) $opt["endTime"] = $endTime;

		$response = $this->httpRequest("v1/klines", "GET", $opt);
		$ticks = $this->chartData($symbol, $interval, $response);
		$this->charts[$symbol][$interval] = $ticks;
		return $ticks;
	}

	// Converts all your balances into a nice array
	// If priceData is passed from $api->prices() it will add btcValue & btcTotal to each symbol
	// This function sets $btc_value which is your estimated BTC value of all assets combined and $btc_total which includes amount on order
	private function balanceData($array, $priceData = false) {
		if ( $priceData ) $btc_value = $btc_total = 0.00;
		$balances = [];
		if ( empty($array) || empty($array['balances']) ) {
			echo "balanceData error: Please make sure your system time is synchronized, or pass the useServerTime option.".PHP_EOL;
			return [];
		}
		foreach ( $array['balances'] as $obj ) {
			$asset = $obj['asset'];
			$balances[$asset] = ["available"=>$obj['free'], "onOrder"=>$obj['locked'], "btcValue"=>0.00000000, "btcTotal"=>0.00000000];
			if ( $priceData ) {
				if ( $obj['free'] + $obj['locked'] < 0.00000001 ) continue;
				if ( $asset == 'BTC' ) {
					$balances[$asset]['btcValue'] = $obj['free'];
					$balances[$asset]['btcTotal'] = $obj['free'] + $obj['locked'];
					$btc_value+= $obj['free'];
					$btc_total+= $obj['free'] + $obj['locked'];
					continue;
				}
				$symbol = $asset.'BTC';
				if ( $symbol == 'USDTBTC' ) {
					$btcValue = number_format($obj['free'] / $priceData['BTCUSDT'],8,'.','');
					$btcTotal = number_format(($obj['free'] + $obj['locked']) / $priceData['BTCUSDT'],8,'.','');
				} elseif ( !isset($priceData[$symbol]) ) {
					$btcValue = $btcTotal = 0;
				} else {
					$btcValue = number_format($obj['free'] * $priceData[$symbol],8,'.','');
					$btcTotal = number_format(($obj['free'] + $obj['locked']) * $priceData[$symbol],8,'.','');
				}
				$balances[$asset]['btcValue'] = $btcValue;
				$balances[$asset]['btcTotal'] = $btcTotal;
				$btc_value+= $btcValue;
				$btc_total+= $btcTotal;
			}
		}
		if ( $priceData ) {
			uasort($balances, function($a, $b) { return $a['btcValue'] < $b['btcValue']; });
			$this->btc_value = $btc_value;
			$this->btc_total = $btc_total;
		}
		return $balances;
	}

	// Convert balance WebSocket data into array
	private function balanceHandler($json) {
		$balances = [];
		foreach ( $json as $item ) {
			$asset = $item->a;
			$available = $item->f;
			$onOrder = $item->l;
			$balances[$asset] = ["available"=>$available, "onOrder"=>$onOrder];
		}
		return $balances;
	}

	// Convert WebSocket ticker data into array
	private function tickerStreamHandler($json) {
		return [
			"eventType" => $json->e,
			"eventTime" => $json->E,
			"symbol" => $json->s,
			"priceChange" => $json->p,
			"percentChange" => $json->P,
			"averagePrice" => $json->w,
			"prevClose" => $json->x,
			"close" => $json->c,
			"closeQty" => $json->Q,
			"bestBid" => $json->b,
			"bestBidQty" => $json->B,
			"bestAsk" => $json->a,
			"bestAskQty" => $json->A,
			"open" => $json->o,
			"high" => $json->h,
			"low" => $json->l,
			"volume" => $json->v,
			"quoteVolume" => $json->q,
			"openTime" => $json->O,
			"closeTime" => $json->C,
			"firstTradeId" => $json->F,
			"lastTradeId" => $json->L,
			"numTrades" => $json->n
		];
	}

	// Convert WebSocket trade execution into array
	private function executionHandler($json) {
		return [
			"symbol" => $json->s,
			"side" => $json->S,
			"orderType" => $json->o,
			"quantity" => $json->q,
			"price" => $json->p,
			"executionType" => $json->x,
			"orderStatus" => $json->X,
			"rejectReason" => $json->r,
			"orderId" => $json->i,
			"clientOrderId" => $json->c,
			"orderTime" => $json->T,
			"eventTime" => $json->E
		];
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
				"volume" => $baseVolume,
				"openTime" =>$openTime,
				"closeTime" =>$closeTime
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

	// Converts depth cache into a cumulative array
	public function cumulative($depth) {
		$bids = []; $asks = [];
		$cumulative = 0;
		foreach ( $depth['bids'] as $price => $quantity ) {
			$cumulative+= $quantity;
			$bids[] = [$price, $cumulative];
		}
		$cumulative = 0;
		foreach ( $depth['asks'] as $price => $quantity ) {
			$cumulative+= $quantity;
			$asks[] = [$price, $cumulative];
		}
		return ["bids"=>$bids, "asks"=>array_reverse($asks)];
	}

	// Converts Chart Data into array for highstock & kline charts
	public function highstock($chart, $include_volume = false) {
		$array = [];
		foreach ( $chart as $timestamp => $obj ) {
			$line = [
				$timestamp,
				floatval($obj['open']),
				floatval($obj['high']),
				floatval($obj['low']),
				floatval($obj['close'])
			];
			if ( $include_volume ) $line[] = floatval($obj['volume']);
			$array[] = $line;
		}
		return $array;
	}

	// Gets first key of an array
	public function first($array) {
		if(count($array)>0)	{
			return array_keys($array)[0];
		}
		return null;
	}

	// Gets last key of an array
	public function last($array) {
		if(count($array)>0)	{
			return array_keys(array_slice($array, -1))[0];
		}
		return null;
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

	// Sorts depth data for display & getting highest bid and lowest ask
	public function sortDepth($symbol, $limit = 11) {
		$bids = $this->depthCache[$symbol]['bids'];
		$asks = $this->depthCache[$symbol]['asks'];
		krsort($bids);
		ksort($asks);
		return ["asks"=> array_slice($asks, 0, $limit, true), "bids"=> array_slice($bids, 0, $limit, true)];
	}

	// Pulls /depth data and subscribes to @depth WebSocket endpoint
	// Maintains a local Depth Cache in sync via lastUpdateId. See depth() and depthHandler()
	public function depthCache($symbols, $callback) {
		if ( !is_array($symbols) ) $symbols = [$symbols];
		$loop = \React\EventLoop\Factory::create();
		$react = new \React\Socket\Connector($loop);
		$connector = new \Ratchet\Client\Connector($loop, $react);
		foreach ( $symbols as $symbol ) {
			if ( !isset($this->info[$symbol]) ) $this->info[$symbol] = [];
			if ( !isset($this->depthQueue[$symbol]) ) $this->depthQueue[$symbol] = [];
			if ( !isset($this->depthCache[$symbol]) ) $this->depthCache[$symbol] = ["bids" => [], "asks" => []];
			$this->info[$symbol]['firstUpdate'] = 0;
			$connector('wss://stream.binance.com:9443/ws/'.strtolower($symbol).'@depth')->then(function($ws) use($callback, $symbol, $loop) {
				$ws->on('message', function($data) use($ws, $callback) {
					$json = json_decode($data, true);
					$symbol = $json['s'];
					if ( $this->info[$symbol]['firstUpdate'] == 0 ) {
						$this->depthQueue[$symbol][] = $json;
						return;
					}
					$this->depthHandler($json);
					call_user_func($callback, $this, $symbol, $this->depthCache[$symbol]);
				});
				$ws->on('close', function($code = null, $reason = null) use($symbol, $loop) {
					echo "depthCache({$symbol}) WebSocket Connection closed! ({$code} - {$reason})".PHP_EOL;
					$loop->stop();
				});
			}, function($e) use($loop) {
				echo "depthCache({$symbol})) Could not connect: {$e->getMessage()}".PHP_EOL;
				$loop->stop();
			});
			$this->depth($symbol);
			foreach ( $this->depthQueue[$symbol] as $data ) {
				$this->depthHandler($json);
			}
			$this->depthQueue[$symbol] = [];
			call_user_func($callback, $this, $symbol, $this->depthCache[$symbol]);
		}
		$loop->run();
	}

	// Trades WebSocket Endpoint
	public function trades($symbols, $callback) {
		if ( !is_array($symbols) ) $symbols = [$symbols];
		$loop = \React\EventLoop\Factory::create();
		$react = new \React\Socket\Connector($loop);
		$connector = new \Ratchet\Client\Connector($loop, $react);
		foreach ( $symbols as $symbol ) {
			if ( !isset($this->info[$symbol]) ) $this->info[$symbol] = [];
			//$this->info[$symbol]['tradesCallback'] = $callback;
			$connector('wss://stream.binance.com:9443/ws/'.strtolower($symbol).'@aggTrade')->then(function($ws) use($callback, $symbol, $loop) {
				$ws->on('message', function($data) use($ws, $callback) {
					$json = json_decode($data, true);
					$symbol = $json['s'];
					$price = $json['p'];
					$quantity = $json['q'];
					$timestamp = $json['T'];
					$maker = $json['m'] ? 'true' : 'false';
					$trades = ["price"=>$price, "quantity"=>$quantity, "timestamp"=>$timestamp, "maker"=>$maker];
					//$this->info[$symbol]['tradesCallback']($this, $symbol, $trades);
					call_user_func($callback, $this, $symbol, $trades);
				});
				$ws->on('close', function($code = null, $reason = null) use($symbol, $loop) {
					echo "trades({$symbol}) WebSocket Connection closed! ({$code} - {$reason})".PHP_EOL;
					$loop->stop();
				});
			}, function($e) use($loop) {
				echo "trades({$symbol}) Could not connect: {$e->getMessage()}".PHP_EOL;
				$loop->stop();
			});
		}
		$loop->run();
	}

	// Pulls 24h price change statistics via WebSocket
	public function ticker($symbol, $callback) {
		$endpoint = $symbol ? strtolower($symbol).'@ticker' : '!ticker@arr';
		\Ratchet\Client\connect('wss://stream.binance.com:9443/ws/'.$endpoint)->then(function($ws) use($callback, $symbol) {
			$ws->on('message', function($data) use($ws, $callback, $symbol) {
				$json = json_decode($data);
				if ( $symbol ) {
					call_user_func($callback, $this, $symbol, $this->tickerStreamHandler($json));
				} else {
					foreach ( $json as $obj ) {
						$return = $this->tickerStreamHandler($obj);
						$symbol = $return['symbol'];
						call_user_func($callback, $this, $symbol, $return);
					}
				}
			});
			$ws->on('close', function($code = null, $reason = null) {
				echo "ticker: WebSocket Connection closed! ({$code} - {$reason})".PHP_EOL;
			});
		}, function($e) {
			echo "ticker: Could not connect: {$e->getMessage()}".PHP_EOL;
		});
	}

	// Pulls /kline data and subscribes to @klines WebSocket endpoint
	public function chart($symbols, $interval = "30m", $callback) {
		if ( !is_array($symbols) ) $symbols = [$symbols];
		$loop = \React\EventLoop\Factory::create();
		$react = new \React\Socket\Connector($loop);
		$connector = new \Ratchet\Client\Connector($loop, $react);
		foreach ( $symbols as $symbol ) {
			if ( !isset($this->charts[$symbol]) ) $this->charts[$symbol] = [];
			$this->charts[$symbol][$interval] = [];
			if ( !isset($this->info[$symbol]) ) $this->info[$symbol] = [];
			if ( !isset($this->info[$symbol][$interval]) ) $this->info[$symbol][$interval] = [];
			if ( !isset($this->chartQueue[$symbol]) ) $this->chartQueue[$symbol] = [];
			$this->chartQueue[$symbol][$interval] = [];
			$this->info[$symbol][$interval]['firstOpen'] = 0;
			//$this->info[$symbol]['chartCallback'.$interval] = $callback;
			$connector('wss://stream.binance.com:9443/ws/'.strtolower($symbol).'@kline_'.$interval)->then(function($ws) use($callback, $symbol, $loop) {
				$ws->on('message', function($data) use($ws, $callback) {
					$json = json_decode($data);
					$chart = $json->k;
					$symbol = $json->s;
					$interval = $chart->i;
					$this->chartHandler($symbol, $interval, $json);
					//$this->info[$symbol]['chartCallback'.$interval]($this, $symbol, $this->charts[$symbol][$interval]);
					call_user_func($callback, $this, $symbol, $this->charts[$symbol][$interval]);
				});
				$ws->on('close', function($code = null, $reason = null) use($symbol, $loop) {
					echo "chart({$symbol},{$interval}) WebSocket Connection closed! ({$code} - {$reason})".PHP_EOL;
					$loop->stop();
				});
			}, function($e) use($loop) {
				echo "chart({$symbol},{$interval})) Could not connect: {$e->getMessage()}".PHP_EOL;
				$loop->stop();
			});
			$this->candlesticks($symbol, $interval);
			foreach ( $this->chartQueue[$symbol][$interval] as $json ) {
				$this->chartHandler($symbol, $interval, $json);
			}
			$this->chartQueue[$symbol][$interval] = [];
			//$this->info[$symbol]['chartCallback'.$interval]($this, $symbol, $this->charts[$symbol][$interval]);
			call_user_func($callback, $this, $symbol, $this->charts[$symbol][$interval]);
		}
		$loop->run();
	}

	// Keep-alive function for userDataStream
	public function keepAlive() {
		$loop = \React\EventLoop\Factory::create();
		$loop->addPeriodicTimer(30, function() {
			$listenKey = $this->options['listenKey'];
			$this->apiRequest("v1/userDataStream?listenKey={$listenKey}", "PUT");
		});
		$loop->run();
	}

	public function miniTicker($callback) {
		\Ratchet\Client\connect('wss://stream2.binance.com:9443/ws/!miniTicker@arr@1000ms')
			->then(function($ws) use($callback) {
			    $ws->on('message', function($data) use($ws, $callback) {
					$json = json_decode($data, true);
					$markets = [];
					foreach ( $json as $obj ) {
						$markets[] = [
							"symbol" => $obj['s'],
							"close" => $obj['c'],
							"open" => $obj['o'],
							"high" => $obj['h'],
							"low" => $obj['l'],
							"volume" => $obj['v'],
							"quoteVolume" => $obj['q'],
							"eventTime" => $obj['E']
						];
					}
					call_user_func($callback, $this, $markets);
				    });
				    $ws->on('close', function($code = null, $reason = null) {
					echo "miniticker: WebSocket Connection closed! ({$code} - {$reason})" . PHP_EOL;
			    });
			}, function($e) {
			    echo "miniticker: Could not connect: {$e->getMessage()}" . PHP_EOL;
			});
	}

	public function getTransfered() {
		$base = log($this->transfered, 1024);
		$suffixes = array('', 'K', 'M', 'G', 'T');
		return round(pow(1024, $base - floor($base)), 2) .' '. $suffixes[floor($base)];
	}

	public function getRequestCount() {
		return $this->requestCount;
	}
}
