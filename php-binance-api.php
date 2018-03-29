<?php
/* ============================================================
 * @package php-binance-api
 * @link https://github.com/jaggedsoft/php-binance-api
 * ============================================================
 * @copyright 2017-2018
 * @author Jon Eyrick
 * @license MIT License
 * ============================================================
 * A curl HTTP REST wrapper for the binance currency exchange
 * */
namespace Binance;
/**
 * Main Binance class
 *
 * Eg. Usage:
 * require 'vendor/autoload.php';
 * $api = new Binance\\API();
 */
class API {
	protected $base = "https://api.binance.com/api/"; ///< REST endpoint for the currency exchange
	protected $wapi = "https://api.binance.com/wapi/"; ///< REST endpoint for the withdrawals
	protected $api_key; ///< API key that you created in the binance website member area
	protected $api_secret; ///< API secret that was given to you when you created the api key
	protected $depthCache = []; ///< Websockets depth cache
	protected $depthQueue = []; ///< Websockets depth queue
	protected $chartQueue = []; ///< Websockets chart queue
	protected $charts = []; ///< Websockets chart data
	protected $info = ["timeOffset"=>0]; ///< Additional connection options
	protected $proxyConf = null; ///< Used for story the proxy configuration
	protected $transfered = 0; ///< This stores the amount of bytes transfered
	protected $requestCount = 0; ///< This stores the amount of API requests
	public $httpDebug = false; ///< If you enable this, curl will output debugging information
	public $balances = []; ///< binace balances from the last run
	public $btc_value = 0.00; ///< value of available assets
	public $btc_total = 0.00; ///< value of available + onOrder assets

	/**
	 * Constructor for the class, There are 4 ways to contruct the class:
	 *- You can use the config file in ~/jaggedsoft/php-binance-api.json and empty contructor
	 *- new Binance\\API( $api_key, $api_secret);
	 *- new Binance\\API( $api_key, $api_secret, $options);
	 *- new Binance\\API( $api_key, $api_secret, $options, $proxyConf);
	 *
	 * @param $api_key string api key
	 * @param $api_secret string api secret
	 * @param $options array addtional coniguration options
	 * @param $proxyConf array config
	 * @return nothing
	 */
	public function __construct($api_key = '', $api_secret = '', $options = ["useServerTime"=>false], $proxyConf = null) {
		$this->api_key = $api_key;
		$this->api_secret = $api_secret;
		$this->proxyConf = $proxyConf;
		if(isset($options['useServerTime']) && $options['useServerTime']) {
			$this->useServerTime();
		}
		$this->setupApiConfigFromFile();
		$this->setupProxyConfigFromFile();
	}

	/**
	 * If no paramaters are supplied in the constructor, this function will attempt
	 * to load the api_key and api_secret from the users home directory in the file
	 * ~/jaggedsoft/php-binance-api.json
	 * @return nothing
	 */
	private function setupApiConfigFromFile()	{
		if( empty( $this->api_key ) == false || empty( $this->api_key ) == false) return;
		if( file_exists( getenv( "HOME" ) . "/.config/jaggedsoft/php-binance-api.json" ) == false ) return;
		$contents = json_decode( file_get_contents( getenv( "HOME" ) . "/.config/jaggedsoft/php-binance-api.json" ), true );
		$this->api_key = isset( $contents['api-key'] ) ? $contents['api-key'] : "";
		$this->api_secret = isset( $contents['api-secret'] ) ? $contents['api-secret'] : "";
	}

	/**
	 * If no paramaters are supplied in the constructor ofr the proxy confguration,
	 * this function will attempt to load the proxy  info from the users home directory
	 * ~/jaggedsoft/php-binance-api.json
	 * @return nothing
	 */
	private function setupProxyConfigFromFile()	{
		if( is_null( $this->proxyConf ) == false ) return;
		if( file_exists( getenv( "HOME" ) . "/.config/jaggedsoft/php-binance-api.json" ) == false ) return;
		$contents = json_decode( file_get_contents( getenv( "HOME" ) . "/.config/jaggedsoft/php-binance-api.json" ), true );
		if( isset( $contents['proto'] ) == false ) return;
		if( isset( $contents['address'] ) == false ) return;
		if( isset( $contents['port'] ) == false ) return;
		$this->proxyConf['proto'] = $contents['proto'];
		$this->proxyConf['address'] = $contents['address'];
		$this->proxyConf['port'] = $contents['port'];
		if( isset( $contents['user'] ) ) {
			$this->proxyConf['user'] = isset( $contents['user'] ) ? $contents['user'] : "";
		}
		if( isset( $contents['pass'] ) ) {
			$this->proxyConf['pass'] = isset( $contents['pass'] ) ? $contents['pass'] : "";
		}
	}

	/**
	 * buy attempts to create a currency order
	 * each currency supports a number of order types, such as
	 * -LIMIT
	 * -MARKET
	 * -STOP_LOSS
	 * -STOP_LOSS_LIMIT
	 * -TAKE_PROFIT
	 * -TAKE_PROFIT_LIMIT
	 * -LIMIT_MAKER
	 *
	 * You should check the @see exchangeInfo for each currency to determine
	 * what types of orders can be placed against specific pairs
	 *
	 * $quantity = 1;
	 * $price = 0.0005;
	 * $order = $api->buy("BNBBTC", $quantity, $price);
	 *
	 * @param $symbol the currency symbol
	 * @param $quantity the quantity required
	 * @param $price price per unit you want to spend
	 * @param $type string type of order
	 * @param $flags array addtional options for order type
	 * @return array with error message or the order details
	 */
	public function buy($symbol, $quantity, $price, $type = "LIMIT", $flags = []) {
		return $this->order("BUY", $symbol, $quantity, $price, $type, $flags);
	}

	/**
	 * buyTest attempts to create a TEST currency order
	 * @see buy()
	 *
	 * @param $symbol the currency symbol
	 * @param $quantity the quantity required
	 * @param $price price per unit you want to spend
	 * @param $type array config
	 * @param $flags array config
	 * @return array with error message or empty or the order details
	 */
	public function buyTest($symbol, $quantity, $price, $type = "LIMIT", $flags = []) {
		return $this->order("BUY", $symbol, $quantity, $price, $type, $flags, true);
	}

	/**
	 * sell attempts to create a currency order
	 * each currency supports a number of order types, such as
	 * -LIMIT
	 * -MARKET
	 * -STOP_LOSS
	 * -STOP_LOSS_LIMIT
	 * -TAKE_PROFIT
	 * -TAKE_PROFIT_LIMIT
	 * -LIMIT_MAKER
	 *
	 * You should check the @see exchangeInfo for each currency to determine
	 * what types of orders can be placed against specific pairs
	 *
	 * $quantity = 1;
	 * $price = 0.0005;
	 * $order = $api->sell("BNBBTC", $quantity, $price);
	 *
	 * @param $symbol the currency symbol
	 * @param $quantity the quantity required
	 * @param $price price per unit you want to spend
	 * @param $type string type of order
	 * @param $flags array addtional options for order type
	 * @return array with error message or the order details
	 */
	public function sell($symbol, $quantity, $price, $type = "LIMIT", $flags = []) {
		return $this->order("SELL", $symbol, $quantity, $price, $type, $flags);
	}

	/**
	 * sellTest attempts to create a TEST currency order
	 * @see sell()
	 *
	 * @param $symbol the currency symbol
	 * @param $quantity the quantity required
	 * @param $price price per unit you want to spend
	 * @param $type array config
	 * @param $flags array config
	 * @return array with error message or empty or the order details
	 */
	public function sellTest($symbol, $quantity, $price, $type = "LIMIT", $flags = []) {
		return $this->order("SELL", $symbol, $quantity, $price, $type, $flags, true);
	}

	/**
	 * marketBuy attempts to create a currency order at given market price
	 *
	 * $quantity = 1;
	 * $order = $api->marketBuy("BNBBTC", $quantity);
	 *
	 * @param $symbol the currency symbol
	 * @param $quantity the quantity required
	 * @param $flags array addtional options for order type
	 * @return array with error message or the order details
	 */
	public function marketBuy($symbol, $quantity, $flags = []) {
		return $this->order("BUY", $symbol, $quantity, 0, "MARKET", $flags);
	}

	/**
	 * marketBuyTest attempts to create a TEST currency order at given market price
	 * @see marketBuy()
	 *
	 * @param $symbol the currency symbol
	 * @param $quantity the quantity required
	 * @param $flags array addtional options for order type
	 * @return array with error message or the order details
	 */
	public function marketBuyTest($symbol, $quantity, $flags = []) {
		return $this->order("BUY", $symbol, $quantity, 0, "MARKET", $flags, true);
	}

	/**
	 * marketSell attempts to create a currency order at given market price
	 *
	 * $quantity = 1;
	 * $order = $api->marketSell("BNBBTC", $quantity);
	 *
	 * @param $symbol the currency symbol
	 * @param $quantity the quantity required
	 * @param $flags array addtional options for order type
	 * @return array with error message or the order details
	 */
	public function marketSell($symbol, $quantity, $flags = []) {
		return $this->order("SELL", $symbol, $quantity, 0, "MARKET", $flags);
	}

	/**
	 * marketSellTest attempts to create a TEST currency order at given market price
	 * @see marketSellTest()
	 *
	 * @param $symbol the currency symbol
	 * @param $quantity the quantity required
	 * @param $flags array addtional options for order type
	 * @return array with error message or the order details
	 */
	public function marketSellTest($symbol, $quantity, $flags = []) {
		return $this->order("SELL", $symbol, $quantity, 0, "MARKET", $flags, true);
	}

	/**
	 * cancel attempts to cancel a currency order
	 *
	 * $orderid = "123456789";
	 * $order = $api->cancel("BNBBTC", $orderid);
	 *
	 * @param $symbol the currency symbol
	 * @param $orderid the orderid to cancel
	 * @return array with error message or the order details
	 */
	public function cancel($symbol, $orderid) {
		return $this->httpRequest("v3/order", "DELETE", ["symbol"=>$symbol, "orderId"=>$orderid], true);
	}

	/**
	 * orderStatus attempts to get orders status
	 *
	 * $orderid = "123456789";
	 * $order = $api->orderStatus("BNBBTC", $orderid);
	 *
	 * @param $symbol the currency symbol
	 * @param $orderid the orderid to cancel
	 * @return array with error message or the order details
	 */
	public function orderStatus($symbol, $orderid) {
		return $this->httpRequest("v3/order", "GET" ,["symbol"=>$symbol, "orderId"=>$orderid], true);
	}

	/**
	 * openOrders attempts to get open orders for all currencies or a specific currency
	 *
	 * $allOpenOrders = $api->openOrders();
	 * $allBNBOrders = $api->openOrders( "BNBBTC" );
	 *
	 * @param $symbol the currency symbol
	 * @return array with error message or the order details
	 */
	public function openOrders($symbol = null) {
		$params = [];
		if( is_null( $symbol ) != true ) {
		    $params = ["symbol"=>$symbol];
		}
		return $this->httpRequest("v3/openOrders","GET", $params, true);
	}

	/**
	 * orders attempts to get the orders for all or a specific currency
	 *
	 * $allBNBOrders = $api->orders( "BNBBTC" );
	 *
	 * @param $symbol the currency symbol
	 * @param $limit the amount of orders returned
	 * @param $fromOrderId return the orders from this order onwards
	 * @return array with error message or array of orderDetails array
	 */
	public function orders($symbol, $limit = 500, $fromOrderId = 1) {
		return $this->httpRequest("v3/allOrders", "GET", ["symbol"=>$symbol, "limit"=>$limit, "orderId"=>$fromOrderId], true);
	}

	/**
	 * history Get the complete account trade history for all or a specific currency
	 *
	 * $allHistory = $api->history();
	 * $BNBHistory = $api->history("BNBBTC");
	 * $limitBNBHistory = $api->history("BNBBTC",5);
	 * $limitBNBHistoryFromId = $api->history("BNBBTC",5,3);
	 *
	 * @param $symbol the currency symbol
	 * @param $limit the amount of orders returned
	 * @param $fromTradeId return the orders from this order onwards
	 * @return array with error message or array of orderDetails array
	 */
	public function history($symbol, $limit = 500, $fromTradeId = 1) {
		return $this->httpRequest("v3/myTrades", "GET", ["symbol"=>$symbol, "limit"=>$limit, "fromId"=>$fromTradeId], true);
	}

	/**
	 * useServerTime adds the 'useServerTime'=>true to the API request to avoid time errors
	 *
	 * $api->useServerTime();
	 *
	 * @return nothing
	 */
	public function useServerTime() {
		$serverTime = $this->httpRequest("v1/time")['serverTime'];
		$this->info['timeOffset'] = $serverTime - (microtime(true)*1000);
	}

	/**
	 * time Gets the server time
	 *
	 * $time = $api->time();
	 *
	 * @return array with error message or array with server time key
	 */
	public function time() {
		return $this->httpRequest("v1/time");
	}

	/**
	 * exchangeInfo Gets the complete exchange info, including limits, currency options etc.
	 *
	 * $info = $api->exchangeInfo();
	 *
	 * @return array with error message or exchange info array
	 */
	public function exchangeInfo() {
		return $this->httpRequest("v1/exchangeInfo");
	}

	/**
	 * withdraw requests a asset be withdrawn from binance to another wallet
	 *
	 * $asset = "BTC";
	 * $address = "1C5gqLRs96Xq4V2ZZAR1347yUCpHie7sa";
	 * $amount = 0.2;
	 * $response = $api->withdraw($asset, $address, $amount);
	 *
	 * $address = "44tLjmXrQNrWJ5NBsEj2R77ZBEgDa3fEe9GLpSf2FRmhexPvfYDUAB7EXX1Hdb3aMQ9FLqdJ56yaAhiXoRsceGJCRS3Jxkn";
	 * $addressTag = "0e5e38a01058dbf64e53a4333a5acf98e0d5feb8e523d32e3186c664a9c762c1";
	 * $amount = 0.1;
	 * $response = $api->withdraw($asset, $address, $amount, $addressTag);
	 *
	 * @param $asset the currency such as BTC
	 * @param $address the addressed to whihc the asset should be deposited
	 * @param $amount the amount of the asset to transfer
	 * @param $addressTag adtional transactionid required by some assets
	 * @return array with error message or array transaction
	 */
	public function withdraw($asset, $address, $amount, $addressTag = false) {
		$options = ["asset"=>$asset, "address"=>$address, "amount"=>$amount, "wapi"=>true, "name"=>"API Withdraw"];
		if ( $addressTag ) $options['addressTag'] = $addressTag;
		return $this->httpRequest("v3/withdraw.html", "POST", $options, true);
	}

	/**
	 * depositAddress get the deposit address for an asset
	 *
	 * $depositAddress = $api->depositAddress("VEN");
	 *
	 * @param $asset the currency such as BTC
	 * @return array with error message or array deposit address information
	 */
	public function depositAddress($asset) {
		$params = ["wapi"=>true, "asset"=>$asset];
		return $this->httpRequest("v3/depositAddress.html", "GET", $params, true);
	}

	/**
	 * depositAddress get the deposit history for an asset
	 *
	 * $depositHistory = $api->depositHistory();
	 *
	 * $depositHistory = $api->depositHistory( "BTC" );
	 *
	 * @param $asset empty or the currency such as BTC
	 * @return array with error message or array deposit history information
	 */
	public function depositHistory($asset = false) {
		$params = ["wapi"=>true];
		if ( is_string( $asset ) == true ) {
			$params['asset'] = $asset;
		}
		return $this->httpRequest("v3/depositHistory.html", "GET", $params, true);
	}

	/**
	 * withdrawHistory get the withdrawal history for an asset
	 *
	 * $withdrawHistory = $api->withdrawHistory();
	 *
	 * $withdrawHistory = $api->withdrawHistory( "BTC" );
	 *
	 * @param $asset empty or the currency such as BTC
	 * @return array with error message or array deposit history information
	 */
	public function withdrawHistory($asset = false) {
		$params = ["wapi"=>true];
		if ( is_string( $asset ) == true ) {
			$params['asset'] = $asset;
		}
		return $this->httpRequest("v3/withdrawHistory.html", "GET", $params, true);
	}

	/**
	 * prices get all the current prices
	 *
	 * $ticker = $api->prices();
	 *
	 * @return array with error message or array of all the currencies prices
	 */
	public function prices() {
		return $this->priceData($this->httpRequest("v3/ticker/price"));
	}

	/**
	 * bookPrices get all bid/asks prices
	 *
	 * $ticker = $api->bookPrices();
	 *
	 * @return array with error message or array of all the book prices
	 */
	public function bookPrices() {
		return $this->bookPriceData($this->httpRequest("v3/ticker/bookTicker"));
	}

	/**
	 * account get all information about the api account
	 *
	 * $account = $api->account();
	 *
	 * @return array with error message or array of all the account information
	 */
	public function account() {
		return $this->httpRequest("v3/account", "GET", [], true);
	}

	/**
	 * prevDay get 24hr ticker price change statistics for a symbol
	 *
	 * $prevDay = $api->prevDay("BNBBTC");
	 *
	 * @param $symbol the symbol to get the previous day change for
	 * @return array with error message or array of prevDay change
	 */
	public function prevDay($symbol) {
		return $this->httpRequest("v1/ticker/24hr", "GET", ["symbol"=>$symbol]);
	}

	/**
	 * aggTrades get Market History / Aggregate Trades
	 *
	 * $trades = $api->aggTrades("BNBBTC");
	 *
	 * @param $symbol the symbol to get the trade information for
	 * @return array with error message or array of market history
	 */
	public function aggTrades($symbol) {
		return $this->tradesData($this->httpRequest("v1/aggTrades", "GET", ["symbol"=>$symbol]));
	}

	/**
	 * depth get Market depth
	 *
	 * $depth = $api->depth("ETHBTC");
	 *
	 * @param $symbol the symbol to get the depth information for
	 * @return array with error message or array of market depth
	 */
	public function depth($symbol) {
		$json = $this->httpRequest("v1/depth", "GET", ["symbol"=>$symbol]);
		if(!isset($this->info[$symbol])) $this->info[$symbol] = [];
		$this->info[$symbol]['firstUpdate'] = $json['lastUpdateId'];
		return $this->depthData($symbol, $json);
	}

	/**
	 * balances get balances for the account assets
	 *
	 * $balances = $api->balances($ticker);
	 *
	 * @param $priceData array of the symbols balances are required for
	 * @return array with error message or array of balances
	 */
	public function balances($priceData = false) {
		return $this->balanceData($this->httpRequest("v3/account", "GET", [], true), $priceData);
	}

	/**
	 * getProxyUriString get Uniform Resource Identifier string assocaited with proxy config
	 *
	 * $balances = $api->getProxyUriString();
	 *
	 * @return string uri
	 */
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

	/**
	 * setProxy set proxy config by passing in an array of the proxy configuration
	 *
	 * $proxyConf = [
	 * 'proto' => 'tcp',
	 * 'address' => '192.168.1.1',
	 * 'port' => '8080',
	 * 'user' => 'dude',
	 * 'pass' => 'd00d'
	 * ];
	 *
	 * $api->setProxy( $proxyconf );
	 *
	 * @return nothing
	 */
	public function setProxy( $proxyconf ) {
		$this->proxyConf = $proxyconf;
	}

	/**
	 * httpRequest curl wrapper for all http api requests.
	 * You can't call this function directly, use the helper functions
	 * @see buy
	 * @see sell
	 * @see marketBuy
	 * @see marketSell
	 *
	 * $this->httpRequest( "https://api.binance.com/api/v1/ticker/24hr");
	 *
	 * @param $url the endpoint to query, typically includes query string
	 * @param $method this should be typically GET, POST or DELETE
	 * @param $params array addtional options for the request
	 * @param $signed bool true or false sign the request with api secret
	 * @return array containing the response
	 */
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

	/**
	 * order formats the orders before sending them to the curl wrapper function
	 * You can call this function directly or use the helper functions
	 * @see buy
	 * @see sell
	 * @see marketBuy
	 * @see marketSell
	 *
	 * $this->httpRequest( "https://api.binance.com/api/v1/ticker/24hr");
	 *
	 * @param $side typically "BUY" or "SELL"
	 * @param $symbol to buy or sell
	 * @param $quantity in the order
	 * @param $price for the order
	 * @param $type is determined by the symbol bu typicall LIMIT, STOP_LOSS_LIMIT etc.
	 * @param $flags additional transaction options
	 * @param $test whether to test or not, test only validates the query
	 * @return array containing the response
	 */
	public function order($side, $symbol, $quantity, $price, $type = "LIMIT", $flags = [], $test = false) {
		$opt = [
			"symbol" => $symbol,
			"side" => $side,
			"type" => $type,
			"quantity" => $quantity,
			"recvWindow" => 60000
		];
		// someone has preformated there 8 decimal point double already
		// dont do anything, leave them do whatever they want
		if ( gettype( $price ) != "string" ) {
			// for every other type, lets format it appropriately
			$price = number_format($price, 8, '.', '');
		}
		if ( $type === "LIMIT" || $type === "STOP_LOSS_LIMIT" || $type === "TAKE_PROFIT_LIMIT" ) {
			$opt["price"] = $price;
			$opt["timeInForce"] = "GTC";
		}
		if ( isset($flags['stopPrice']) ) $opt['stopPrice'] = $flags['stopPrice'];
		if ( isset($flags['icebergQty']) ) $opt['icebergQty'] = $flags['icebergQty'];
		if ( isset($flags['newOrderRespType']) ) $opt['newOrderRespType'] = $flags['newOrderRespType'];
		$qstring = ( $test == false ) ? "v3/order" : "v3/order/test";
		return $this->httpRequest($qstring, "POST", $opt, true);
	}

	/**
	 * candlesticks get the cancles for the given intervals
	 * 1m,3m,5m,15m,30m,1h,2h,4h,6h,8h,12h,1d,3d,1w,1M
	 *
	 * $candles = $api->candlesticks("BNBBTC", "5m");
	 *
	 * @param $symbol to query
	 * @param $interval to request
	 * @param $limit limit the amount of candles
	 * @param $startTime request candle information starting from here
	 * @param $endTime request candle information ending here
	 * @return array containing the response
	 */
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

	/**
	 * balanceData Converts all your balances into a nice array
	 * If priceData is passed from $api->prices() it will add btcValue & btcTotal to each symbol
	 * This function sets $btc_value which is your estimated BTC value of all assets combined and $btc_total which includes amount on order
	 *
	 * $candles = $api->candlesticks("BNBBTC", "5m");
	 *
	 * @param $array of your balances
	 * @param $priceData array of prices
	 * @return array containing the response
	 */
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

	/**
	 * balanceHandler Convert balance WebSocket data into array
	 *
	 * $data = $this->balanceHandler( $json );
	 *
	 * @param $json data to convert
	 * @return array
	 */
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

	/**
	 * tickerStreamHandler Convert WebSocket ticker data into array
	 *
	 * $data = $this->tickerStreamHandler( $json );
	 *
	 * @param $json data to convert
	 * @return array
	 */
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

	/**
	 * tickerStreamHandler Convert WebSocket trade execution into array
	 *
	 * $data = $this->executionHandler( $json );
	 *
	 * @param $json data to convert
	 * @return array
	 */
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

	/**
	 * chartData Convert kline data into object
	 *
	 * $object = $this->chartData($symbol, $interval, $ticks);
	 *
	 * @param $symbol of your currency
	 * @param $interval the time interval
	 * @param $ticks the canbles array
	 * @return array object of the chartdata
	 */
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

	/**
	 * tradesData Convert aggTrades data into easier format
	 *
	 * $tradesData = $this->tradesData($trades);
	 *
	 * @param $trades array of trade information
	 * @return array easier format for trade information
	 */
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

	/**
	 * bookPriceData Consolidates Book Prices into an easy to use object
	 *
	 * $bookPriceData = $this->bookPriceData($array);
	 *
	 * @param $array book prices
	 * @return array easier format for book prices information
	 */
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

	/**
	 * priceData Converts Price Data into an easy key/value array
	 *
	 * $array = $this->priceData($array);
	 *
	 * @param $array of prices
	 * @return array of key/value pairs
	 */
	private function priceData($array) {
		$prices = [];
		foreach ( $array as $obj ) {
			$prices[$obj['symbol']] = $obj['price'];
		}
		return $prices;
	}

	/**
	 * cumulative Converts depth cache into a cumulative array
	 *
	 * $cumulative = $api->cumulative($depth);
	 *
	 * @param $depth cache array
	 * @return array cumulative depth cache
	 */
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

	/**
	 * highstock Converts Chart Data into array for highstock & kline charts
	 *
	 * $highstock = $api->highstock($chart, $include_volume);
	 *
	 * @param $chart array
	 * @param $include_volume bool for inclusion of volume
	 * @return array highchart data
	 */
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

	/**
	 * first Gets first key of an array
	 *
	 * $first = $api->first($array);
	 *
	 * @param $array array
	 * @return string key or null
	 */
	public function first($array) {
		if(count($array)>0)	{
			return array_keys($array)[0];
		}
		return null;
	}

	/**
	 * last Gets last key of an array
	 *
	 * $last = $api->last($array);
	 *
	 * @param $array array
	 * @return string key or null
	 */
	public function last($array) {
		if(count($array)>0)	{
			return array_keys(array_slice($array, -1))[0];
		}
		return null;
	}

	/**
	 * displayDepth Formats nicely for console output
	 *
	 * $outputString = $api->displayDepth($array);
	 *
	 * @param $array array
	 * @return string of the depth information
	 */
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

	/**
	 * depthData Formats depth data for nice display
	 *
	 * $array = $this->depthData($symbol, $json);
	 *
	 * @param $symbol to display
	 * @param $json array of the depth infomration
	 * @return array of the depth information
	 */
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

	/**
	 * getTransfered gets the total transfered in b,Kb,Mb,Gb
	 *
	 * $transfered = $api->getTransfered();
	 *
	 * @return string showing the total transfered
	 */
	public function getTransfered() {
		$base = log($this->transfered, 1024);
		$suffixes = array('', 'K', 'M', 'G', 'T');
		return round(pow(1024, $base - floor($base)), 2) .' '. $suffixes[floor($base)];
	}

	/**
	 * getRequestCount gets the total number of API calls
	 *
	 * $apiCount = $api->getRequestCount();
	 *
	 * @return int get the total number of api calls
	 */
	public function getRequestCount() {
		return $this->requestCount;
	}

	/**
	 * getRequestCount gets the total number of API calls
	 *
	 * $apiCount = $api->getRequestCount();
	 *
	 * @return int get the total number of api calls
	 */
	public function getRequestCount() {
		return $this->requestCount;
	}

	/**
	 * addToTransfered add interger bytes to the total transfered
	 * also incrementes the api counter
	 *
	 * $apiCount = $api->addToTransfered( $int );
	 *
	 * @return nothing
	 */
	public function addToTransfered( $int ) {
		$this->transfered += $int;
		$this->requestCount++;
	}

	/*
	* WebSockets
	*/

	/**
	 * depthHandler For WebSocket Depth Cache
	 *
	 * $this->depthHandler($json);
	 *
	 * @param $symbol to sort
	 * @param $interval time
	 * @param $json time
	 * @return nothing
	 */
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

	/**
	 * chartHandler For WebSocket Chart Cache
	 *
	 * $this->chartHandler($symbol, $interval, $json);
	 *
	 * @param $symbol to sort
	 * @param $interval time
	 * @param $json time
	 * @return nothing
	 */
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

	/**
	 * sortDepth Sorts depth data for display & getting highest bid and lowest ask
	 *
	 * $sorted = $api->sortDepth($symbol, $limit);
	 *
	 * @param $symbol to sort
	 * @param $limit depth
	 * @return nothing
	 */
	public function sortDepth($symbol, $limit = 11) {
		$bids = $this->depthCache[$symbol]['bids'];
		$asks = $this->depthCache[$symbol]['asks'];
		krsort($bids);
		ksort($asks);
		return ["asks"=> array_slice($asks, 0, $limit, true), "bids"=> array_slice($bids, 0, $limit, true)];
	}

	/**
	 * depthCache Pulls /depth data and subscribes to @depth WebSocket endpoint
	 * Maintains a local Depth Cache in sync via lastUpdateId. See depth() and depthHandler()
	 *
	 * $api->depthCache(["BNBBTC"], function($api, $symbol, $depth) {
	 * 	echo "{$symbol} depth cache update".PHP_EOL;
	 * 	//print_r($depth); // Print all depth data
	 * 	$limit = 11; // Show only the closest asks/bids
	 * 	$sorted = $api->sortDepth($symbol, $limit);
	 * 	$bid = $api->first($sorted['bids']);
	 * 	$ask = $api->first($sorted['asks']);
	 * 	echo $api->displayDepth($sorted);
	 * 	echo "ask: {$ask}".PHP_EOL;
	 * 	echo "bid: {$bid}".PHP_EOL;
	 * });
	 *
	 * @param $symbol optional array of symbols
	 * @param $callback closure
	 * @return nothing
	 */
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

	/**
	 * trades Trades WebSocket Endpoint
	 *
	 * $api->trades(["BNBBTC"], function($api, $symbol, $trades) {
	 *     echo "{$symbol} trades update".PHP_EOL;
	 *     print_r($trades);
	 * });
	 *
	 * @param $symbol optional symbol
	 * @param $callback closure
	 * @return nothing
	 */
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

	/**
	 * ticker pulls 24h price change statistics via WebSocket
	 *
	 * $api->ticker(false, function($api, $symbol, $ticker) {
	 * 	print_r($ticker);
	 * });
	 *
	 * @param $symbol optional symbol or false
	 * @param $callback closure
	 * @return nothing
	 */
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

	/**
	 * chart Pulls /kline data and subscribes to @klines WebSocket endpoint
	 *
	 * $api->chart(["BNBBTC"], "15m", function($api, $symbol, $chart) {
	 *     echo "{$symbol} chart update\n";
	 *     print_r($chart);
	 * });
	 *
	 * @param $symbols required symbols
	 * @param $interval time inteval
	 * @param $callback closure
	 * @return nothing
	 */
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

	/**
	 * keepAlive Keep-alive function for userDataStream
	 *
	 * $api->keepAlive();
	 *
	 * @return nothing
	 */
	public function keepAlive() {
		$loop = \React\EventLoop\Factory::create();
		$loop->addPeriodicTimer(30, function() {
			$listenKey = $this->options['listenKey'];
			$this->httpRequest("v1/userDataStream?listenKey={$listenKey}", "PUT", []);
		});
		$loop->run();
	}

	/**
	 * userData Issues userDataStream token and keepalive, subscribes to userData WebSocket
	 *
	 * $balance_update = function($api, $balances) {
	 *  	print_r($balances);
	 *  	echo "Balance update".PHP_EOL;
	 * };

	 * $order_update = function($api, $report) {
	 *  echo "Order update".PHP_EOL;
	 *  print_r($report);
	 *  $price = $report['price'];
	 *  $quantity = $report['quantity'];
	 *  $symbol = $report['symbol'];
	 *  $side = $report['side'];
	 *  $orderType = $report['orderType'];
	 *  $orderId = $report['orderId'];
	 *  $orderStatus = $report['orderStatus'];
	 *  $executionType = $report['orderStatus'];
	 *  if ( $executionType == "NEW" ) {
	 *    if ( $executionType == "REJECTED" ) {
	 *    	echo "Order Failed! Reason: {$report['rejectReason']}".PHP_EOL;
	 * 	}
	 * 	echo "{$symbol} {$side} {$orderType} ORDER #{$orderId} ({$orderStatus})".PHP_EOL;
	 * 	echo "..price: {$price}, quantity: {$quantity}".PHP_EOL;
	 *    return;
	 *  }
	 *
	 *  //NEW, CANCELED, REPLACED, REJECTED, TRADE, EXPIRED
	 *  echo "{$symbol} {$side} {$executionType} {$orderType} ORDER #{$orderId}".PHP_EOL;
	 * };
	 * $api->userData($balance_update, $order_update);
	 *
	 * @param $balance_callback function
	 * @param $execution_callback function
	 * @return nothing
	 */
	public function userData(&$balance_callback, &$execution_callback = false) {
		$response = $this->httpRequest("v1/userDataStream", "POST", []);
		$listenKey = $this->options['listenKey'] = $response['listenKey'];
		$this->info['balanceCallback'] = $balance_callback;
		$this->info['executionCallback'] = $execution_callback;
		\Ratchet\Client\connect('wss://stream.binance.com:9443/ws/'.$listenKey)->then(function($ws) {
			$ws->on('message', function($data) use($ws) {
				$json = json_decode($data);
				$type = $json->e;
				if ( $type == "outboundAccountInfo") {
					$balances = $this->balanceHandler($json->B);
					$this->info['balanceCallback']($this, $balances);
				} elseif ( $type == "executionReport" ) {
					$report = $this->executionHandler($json);
					if ( $this->info['executionCallback'] ) {
						$this->info['executionCallback']($this, $report);
					}
				}
			});
			$ws->on('close', function($code = null, $reason = null) {
				echo "userData: WebSocket Connection closed! ({$code} - {$reason})".PHP_EOL;
			});
		}, function($e) {
			echo "userData: Could not connect: {$e->getMessage()}".PHP_EOL;
		});
	}

	/**
	 * miniTicker Get miniTicker for all symbols
	 *
	 * $api->miniTicker(function($api, $ticker) {
	 *   print_r($ticker);
    * });
	 * @param $callback function closer that takes 2 arguments, $pai and $ticker data
	 * @return nothing
	 */
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
}
