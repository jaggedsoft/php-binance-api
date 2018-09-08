<?php

/*
 * ============================================================
 * @package php-binance-api
 * @link https://github.com/jaggedsoft/php-binance-api
 * ============================================================
 * @copyright 2017-2018
 * @author Jon Eyrick
 * @license MIT License
 * ============================================================
 * A curl HTTP REST wrapper for the binance currency exchange
 */
namespace Binance;

// PHP version check
if (version_compare(phpversion(), '7.0', '<=')) {
    fwrite(STDERR, "Hi, PHP " . phpversion() . " support will be removed very soon as part of continued development.\n");
    fwrite(STDERR, "Please consider upgrading.\n");
}

/**
 * Main Binance class
 *
 * Eg. Usage:
 * require 'vendor/autoload.php';
 * $api = new Binance\\API();
 */
class API
{
    protected $base = 'https://api.binance.com/api/'; // /< REST endpoint for the currency exchange
    protected $wapi = 'https://api.binance.com/wapi/'; // /< REST endpoint for the withdrawals
    protected $stream = 'wss://stream.binance.com:9443/ws/'; // /< Endpoint for establishing websocket connections
    protected $api_key; // /< API key that you created in the binance website member area
    protected $api_secret; // /< API secret that was given to you when you created the api key
    protected $depthCache = []; // /< Websockets depth cache
    protected $depthQueue = []; // /< Websockets depth queue
    protected $chartQueue = []; // /< Websockets chart queue
    protected $charts = []; // /< Websockets chart data
    protected $curlOpts = []; // /< User defined curl coptions
    protected $info = [
        "timeOffset" => 0,
    ]; // /< Additional connection options
    protected $proxyConf = null; // /< Used for story the proxy configuration
    protected $caOverride = false; // /< set this if you donnot wish to use CA bundle auto download feature
    protected $transfered = 0; // /< This stores the amount of bytes transfered
    protected $requestCount = 0; // /< This stores the amount of API requests
    private $httpDebug = false; // /< If you enable this, curl will output debugging information
    private $subscriptions = []; // /< View all websocket subscriptions
    private $btc_value = 0.00; // /< value of available assets
    private $btc_total = 0.00;

    // /< value of available onOrder assets

    /**
     * Constructor for the class,
     * send as many argument as you want.
     *
     * No arguments - use file setup
     * 1 argument - file to load config from
     * 2 arguments - api key and api secret
     *
     * @return null
     */
    public function __construct()
    {
        $param = func_get_args();
        switch (func_num_args()) {
            case 0:
                $this->setupApiConfigFromFile();
                $this->setupProxyConfigFromFile();
                $this->setupCurlOptsFromFile();
                break;
            case 1:
                $this->setupApiConfigFromFile($param[0]);
                $this->setupProxyConfigFromFile($param[0]);
                $this->setupCurlOptsFromFile($param[0]);
                break;
            case 2:
                $this->api_key = $param[0];
                $this->api_secret = $param[1];
                break;
            default:
                echo 'Please see valid constructors here: https://github.com/jaggedsoft/php-binance-api/blob/master/examples/constructor.php';
        }
    }

    /**
     * magic get for private and protected members
     *
     * @param $file string the name of the property to return
     * @return null
     */
    public function __get(string $member)
    {
        if (property_exists($this, $member)) {
            return $this->$member;
        }
        return null;
    }

    /**
     * magic set for private and protected members
     *
     * @param $member string the name of the member property
     * @param $value the value of the member property
     */
    public function __set(string $member, $value)
    {
        $this->$member = $value;
    }

    /**
     * If no paramaters are supplied in the constructor, this function will attempt
     * to load the api_key and api_secret from the users home directory in the file
     * ~/jaggedsoft/php-binance-api.json
     *
     * @param $file string file location
     * @return null
     */
    private function setupApiConfigFromFile(string $file = null)
    {
        $file = is_null($file) ? getenv("HOME") . "/.config/jaggedsoft/php-binance-api.json" : $file;

        if (empty($this->api_key) === false || empty($this->api_key) === false) {
            return;
        }
        if (file_exists($file) === false) {
            echo "Unable to load config from: " . $file . PHP_EOL;
            echo "Detected no API KEY or SECRET, all signed requests will fail" . PHP_EOL;
            return;
        }
        $contents = json_decode(file_get_contents($file), true);
        $this->api_key = isset($contents['api-key']) ? $contents['api-key'] : "";
        $this->api_secret = isset($contents['api-secret']) ? $contents['api-secret'] : "";
    }

    /**
     * If no paramaters are supplied in the constructor, this function will attempt
     * to load the acurlopts from the users home directory in the file
     * ~/jaggedsoft/php-binance-api.json
     *
     * @param $file string file location
     * @return null
     */
    private function setupCurlOptsFromFile(string $file = null)
    {
        $file = is_null($file) ? getenv("HOME") . "/.config/jaggedsoft/php-binance-api.json" : $file;

        if (count($this->curlOpts) > 0) {
            return;
        }
        if (file_exists($file) === false) {
            echo "Unable to load config from: " . $file . PHP_EOL;
            echo "No curl options will be set" . PHP_EOL;
            return;
        }
        $contents = json_decode(file_get_contents($file), true);
        $this->curlOpts = isset($contents['curlOpts']) && is_array($contents['curlOpts']) ? $contents['curlOpts'] : [];
    }

    /**
     * If no paramaters are supplied in the constructor for the proxy confguration,
     * this function will attempt to load the proxy info from the users home directory
     * ~/jaggedsoft/php-binance-api.json
     *
     * @return null
     */
    private function setupProxyConfigFromFile(string $file = null)
    {
        $file = is_null($file) ? getenv("HOME") . "/.config/jaggedsoft/php-binance-api.json" : $file;

        if (is_null($this->proxyConf) === false) {
            return;
        }
        if (file_exists($file) === false) {
            echo "Unable to load config from: " . $file . PHP_EOL;
            echo "No proxies will be used " . PHP_EOL;
            return;
        }
        $contents = json_decode(file_get_contents($file), true);
        if (isset($contents['proto']) === false) {
            return;
        }
        if (isset($contents['address']) === false) {
            return;
        }
        if (isset($contents['port']) === false) {
            return;
        }
        $this->proxyConf['proto'] = $contents['proto'];
        $this->proxyConf['address'] = $contents['address'];
        $this->proxyConf['port'] = $contents['port'];
        if (isset($contents['user'])) {
            $this->proxyConf['user'] = isset($contents['user']) ? $contents['user'] : "";
        }
        if (isset($contents['pass'])) {
            $this->proxyConf['pass'] = isset($contents['pass']) ? $contents['pass'] : "";
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
     * @param $symbol string the currency symbol
     * @param $quantity string the quantity required
     * @param $price string price per unit you want to spend
     * @param $type string type of order
     * @param $flags array addtional options for order type
     * @return array with error message or the order details
     */
    public function buy(string $symbol, $quantity, $price, string $type = "LIMIT", array $flags = [])
    {
        return $this->order("BUY", $symbol, $quantity, $price, $type, $flags);
    }

    /**
     * buyTest attempts to create a TEST currency order
     *
     * @see buy()
     *
     * @param $symbol string the currency symbol
     * @param $quantity string the quantity required
     * @param $price string price per unit you want to spend
     * @param $type string config
     * @param $flags array config
     * @return array with error message or empty or the order details
     */
    public function buyTest(string $symbol, $quantity, $price, string $type = "LIMIT", array $flags = [])
    {
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
     * @param $symbol string the currency symbol
     * @param $quantity string the quantity required
     * @param $price string price per unit you want to spend
     * @param $type string type of order
     * @param $flags array addtional options for order type
     * @return array with error message or the order details
     */
    public function sell(string $symbol, $quantity, $price, string $type = "LIMIT", array $flags = [])
    {
        return $this->order("SELL", $symbol, $quantity, $price, $type, $flags);
    }

    /**
     * sellTest attempts to create a TEST currency order
     *
     * @see sell()
     *
     * @param $symbol string the currency symbol
     * @param $quantity string the quantity required
     * @param $price string price per unit you want to spend
     * @param $type array config
     * @param $flags array config
     * @return array with error message or empty or the order details
     */
    public function sellTest(string $symbol, $quantity, $price, string $type = "LIMIT", array $flags = [])
    {
        return $this->order("SELL", $symbol, $quantity, $price, $type, $flags, true);
    }

    /**
     * marketBuy attempts to create a currency order at given market price
     *
     * $quantity = 1;
     * $order = $api->marketBuy("BNBBTC", $quantity);
     *
     * @param $symbol string the currency symbol
     * @param $quantity string the quantity required
     * @param $flags array addtional options for order type
     * @return array with error message or the order details
     */
    public function marketBuy(string $symbol, $quantity, array $flags = [])
    {
        return $this->order("BUY", $symbol, $quantity, 0, "MARKET", $flags);
    }

    /**
     * marketBuyTest attempts to create a TEST currency order at given market price
     *
     * @see marketBuy()
     *
     * @param $symbol string the currency symbol
     * @param $quantity string the quantity required
     * @param $flags array addtional options for order type
     * @return array with error message or the order details
     */
    public function marketBuyTest(string $symbol, $quantity, array $flags = [])
    {
        return $this->order("BUY", $symbol, $quantity, 0, "MARKET", $flags, true);
    }

    /**
     * marketSell attempts to create a currency order at given market price
     *
     * $quantity = 1;
     * $order = $api->marketSell("BNBBTC", $quantity);
     *
     * @param $symbol string the currency symbol
     * @param $quantity string the quantity required
     * @param $flags array addtional options for order type
     * @return array with error message or the order details
     */
    public function marketSell(string $symbol, $quantity, array $flags = [])
    {
        return $this->order("SELL", $symbol, $quantity, 0, "MARKET", $flags);
    }

    /**
     * marketSellTest attempts to create a TEST currency order at given market price
     *
     * @see marketSellTest()
     *
     * @param $symbol string the currency symbol
     * @param $quantity string the quantity required
     * @param $flags array addtional options for order type
     * @return array with error message or the order details
     */
    public function marketSellTest(string $symbol, $quantity, array $flags = [])
    {
        return $this->order("SELL", $symbol, $quantity, 0, "MARKET", $flags, true);
    }

    /**
     * cancel attempts to cancel a currency order
     *
     * $orderid = "123456789";
     * $order = $api->cancel("BNBBTC", $orderid);
     *
     * @param $symbol string the currency symbol
     * @param $orderid string the orderid to cancel
     * @return array with error message or the order details
     * @throws \Exception
     */
    public function cancel(string $symbol, $orderid)
    {
        return $this->httpRequest("v3/order", "DELETE", [
            "symbol" => $symbol,
            "orderId" => $orderid,
        ], true);
    }

    /**
     * orderStatus attempts to get orders status
     *
     * $orderid = "123456789";
     * $order = $api->orderStatus("BNBBTC", $orderid);
     *
     * @param $symbol string the currency symbol
     * @param $orderid string the orderid to cancel
     * @return array with error message or the order details
     * @throws \Exception
     */
    public function orderStatus(string $symbol, $orderid)
    {
        return $this->httpRequest("v3/order", "GET", [
            "symbol" => $symbol,
            "orderId" => $orderid,
        ], true);
    }

    /**
     * openOrders attempts to get open orders for all currencies or a specific currency
     *
     * $allOpenOrders = $api->openOrders();
     * $allBNBOrders = $api->openOrders( "BNBBTC" );
     *
     * @param $symbol string the currency symbol
     * @return array with error message or the order details
     * @throws \Exception
     */
    public function openOrders(string $symbol = null)
    {
        $params = [];
        if (is_null($symbol) != true) {
            $params = [
                "symbol" => $symbol,
            ];
        }
        return $this->httpRequest("v3/openOrders", "GET", $params, true);
    }

    /**
     * orders attempts to get the orders for all or a specific currency
     *
     * $allBNBOrders = $api->orders( "BNBBTC" );
     *
     * @param $symbol string the currency symbol
     * @param $limit int the amount of orders returned
     * @param $fromOrderId string return the orders from this order onwards
     * @return array with error message or array of orderDetails array
     * @throws \Exception
     */
    public function orders(string $symbol, int $limit = 500, int $fromOrderId = 1)
    {
        return $this->httpRequest("v3/allOrders", "GET", [
            "symbol" => $symbol,
            "limit" => $limit,
            "orderId" => $fromOrderId,
        ], true);
    }

    /**
     * history Get the complete account trade history for all or a specific currency
     *
     * $allHistory = $api->history();
     * $BNBHistory = $api->history("BNBBTC");
     * $limitBNBHistory = $api->history("BNBBTC",5);
     * $limitBNBHistoryFromId = $api->history("BNBBTC",5,3);
     *
     * @param $symbol string the currency symbol
     * @param $limit int the amount of orders returned
     * @param $fromTradeId int (optional) return the orders from this order onwards. negative for all
     * @return array with error message or array of orderDetails array
     * @throws \Exception
     */
    public function history(string $symbol, int $limit = 500, int $fromTradeId = -1)
    {
        $parameters = [
            "symbol" => $symbol,
            "limit" => $limit,
        ];
        if ($fromTradeId > 0) {
            $parameters["fromId"] = $fromTradeId;
        }

        return $this->httpRequest("v3/myTrades", "GET", $parameters, true);
    }

    /**
     * useServerTime adds the 'useServerTime'=>true to the API request to avoid time errors
     *
     * $api->useServerTime();
     *
     * @return null
     * @throws \Exception
     */
    public function useServerTime()
    {
        $request = $this->httpRequest("v1/time");
        if (isset($request['serverTime'])) {
            $this->info['timeOffset'] = $request['serverTime'] - (microtime(true) * 1000);
        }
    }

    /**
     * time Gets the server time
     *
     * $time = $api->time();
     *
     * @return array with error message or array with server time key
     * @throws \Exception
     */
    public function time()
    {
        return $this->httpRequest("v1/time");
    }

    /**
     * exchangeInfo Gets the complete exchange info, including limits, currency options etc.
     *
     * $info = $api->exchangeInfo();
     *
     * @return array with error message or exchange info array
     * @throws \Exception
     */
    public function exchangeInfo()
    {
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
     * @param $asset string the currency such as BTC
     * @param $address string the addressed to whihc the asset should be deposited
     * @param $amount double the amount of the asset to transfer
     * @param $addressTag string adtional transactionid required by some assets
     * @return array with error message or array transaction
     * @throws \Exception
     */
    public function withdraw(string $asset, string $address, $amount, $addressTag = null)
    {
        $options = [
            "asset" => $asset,
            "address" => $address,
            "amount" => $amount,
            "wapi" => true,
            "name" => "API Withdraw",
        ];
        if (is_null($addressTag) === false && is_empty($addressTag) === false) {
            $options['addressTag'] = $addressTag;
        }
        return $this->httpRequest("v3/withdraw.html", "POST", $options, true);
    }

    /**
     * depositAddress get the deposit address for an asset
     *
     * $depositAddress = $api->depositAddress("VEN");
     *
     * @param $asset string the currency such as BTC
     * @return array with error message or array deposit address information
     * @throws \Exception
     */
    public function depositAddress(string $asset)
    {
        $params = [
            "wapi" => true,
            "asset" => $asset,
        ];
        return $this->httpRequest("v3/depositAddress.html", "GET", $params, true);
    }

    /**
     * depositAddress get the deposit history for an asset
     *
     * $depositHistory = $api->depositHistory();
     *
     * $depositHistory = $api->depositHistory( "BTC" );
     *
     * @param $asset string empty or the currency such as BTC
     * @param $params array optional startTime, endTime, status parameters
     * @return array with error message or array deposit history information
     * @throws \Exception
     */
    public function depositHistory(string $asset = null, array $params = [])
    {
        $params["wapi"] = true;
        if (is_null($asset) === false) {
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
     * @param $asset string empty or the currency such as BTC
     * @param $params array optional startTime, endTime, status parameters
     * @return array with error message or array deposit history information
     * @throws \Exception
     */
    public function withdrawHistory(string $asset = null, array $params = [])
    {
        $params["wapi"] = true;
        if (is_null($asset) === false) {
            $params['asset'] = $asset;
        }
        return $this->httpRequest("v3/withdrawHistory.html", "GET", $params, true);
    }

    /**
     * withdrawFee get the withdrawal fee for an asset
     *
     * $withdrawFee = $api->withdrawFee( "BTC" );
     *
     * @param $asset string currency such as BTC
     * @return array with error message or array containing withdrawFee
     * @throws \Exception
     */
    public function withdrawFee(string $asset)
    {
        $params = [
            "wapi" => true,
            "asset" => $asset,
        ];
        return $this->httpRequest("v3/withdrawFee.html", "GET", $params, true);
    }

    /**
     * prices get all the current prices
     *
     * $ticker = $api->prices();
     *
     * @return array with error message or array of all the currencies prices
     * @throws \Exception
     */
    public function prices()
    {
        return $this->priceData($this->httpRequest("v3/ticker/price"));
    }

    /**
     * price get the latest price of a symbol
     *
     * $price = $api->price( "ETHBTC" );
     *
     * @return array with error message or array with symbol price
     * @throws \Exception
     */
    public function price(string $symbol)
    {
        $ticker = $this->httpRequest("v3/ticker/price", "GET", ["symbol" => $symbol]);

        return $ticker['price'];
    }

    /**
     * bookPrices get all bid/asks prices
     *
     * $ticker = $api->bookPrices();
     *
     * @return array with error message or array of all the book prices
     * @throws \Exception
     */
    public function bookPrices()
    {
        return $this->bookPriceData($this->httpRequest("v3/ticker/bookTicker"));
    }

    /**
     * account get all information about the api account
     *
     * $account = $api->account();
     *
     * @return array with error message or array of all the account information
     * @throws \Exception
     */
    public function account()
    {
        return $this->httpRequest("v3/account", "GET", [], true);
    }

    /**
     * prevDay get 24hr ticker price change statistics for symbols
     *
     * $prevDay = $api->prevDay("BNBBTC");
     *
     * @param $symbol (optional) symbol to get the previous day change for
     * @return array with error message or array of prevDay change
     * @throws \Exception
     */
    public function prevDay(string $symbol = null)
    {
        $additionalData = [];
        if (is_null($symbol) === false) {
            $additionalData = [
                'symbol' => $symbol,
            ];
        }
        return $this->httpRequest("v1/ticker/24hr", "GET", $additionalData);
    }

    /**
     * aggTrades get Market History / Aggregate Trades
     *
     * $trades = $api->aggTrades("BNBBTC");
     *
     * @param $symbol string the symbol to get the trade information for
     * @return array with error message or array of market history
     * @throws \Exception
     */
    public function aggTrades(string $symbol)
    {
        return $this->tradesData($this->httpRequest("v1/aggTrades", "GET", [
            "symbol" => $symbol,
        ]));
    }

    /**
     * depth get Market depth
     *
     * $depth = $api->depth("ETHBTC");
     *
     * @param $symbol string the symbol to get the depth information for
     * @return array with error message or array of market depth
     * @throws \Exception
     */
    public function depth(string $symbol)
    {
        if (isset($symbol) === false || is_string($symbol) === false) {
            // WPCS: XSS OK.
            echo "asset: expected bool false, " . gettype($symbol) . " given" . PHP_EOL;
        }
        $json = $this->httpRequest("v1/depth", "GET", [
            "symbol" => $symbol,
        ]);
        if (isset($this->info[$symbol]) === false) {
            $this->info[$symbol] = [];
        }
        $this->info[$symbol]['firstUpdate'] = $json['lastUpdateId'];
        return $this->depthData($symbol, $json);
    }

    /**
     * balances get balances for the account assets
     *
     * $balances = $api->balances($ticker);
     *
     * @param bool $priceData array of the symbols balances are required for
     * @return array with error message or array of balances
     * @throws \Exception
     */
    public function balances($priceData = false)
    {
        if (is_array($priceData) === false) {
            $priceData = false;
        }

        $account = $this->httpRequest("v3/account", "GET", [], true);

        if (is_array($account) === false) {
            echo "Error: unable to fetch your account details" . PHP_EOL;
        }

        if (isset($account['balances']) === false) {
            echo "Error: your balances were empty or unset" . PHP_EOL;
        }

        return $this->balanceData($account, $priceData);
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
        $uri = isset($this->proxyConf['proto']) ? $this->proxyConf['proto'] : "http";
        // https://curl.haxx.se/libcurl/c/CURLOPT_PROXY.html
        $supportedProtocols = array(
            'http',
            'https',
            'socks4',
            'socks4a',
            'socks5',
            'socks5h',
        );

        if (in_array($uri, $supportedProtocols) === false) {
            // WPCS: XSS OK.
            echo "Unknown proxy protocol '" . $this->proxyConf['proto'] . "', supported protocols are " . implode(", ", $supportedProtocols) . PHP_EOL;
        }

        $uri .= "://";
        $uri .= isset($this->proxyConf['address']) ? $this->proxyConf['address'] : "localhost";

        if (isset($this->proxyConf['address']) === false) {
            // WPCS: XSS OK.
            echo "warning: proxy address not set defaulting to localhost" . PHP_EOL;
        }

        $uri .= ":";
        $uri .= isset($this->proxyConf['port']) ? $this->proxyConf['port'] : "1080";

        if (isset($this->proxyConf['address']) === false) {
            // WPCS: XSS OK.
            echo "warning: proxy port not set defaulting to 1080" . PHP_EOL;
        }

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
     * @return null
     */
    public function setProxy(array $proxyconf)
    {
        $this->proxyConf = $proxyconf;
    }

    /**
     * httpRequest curl wrapper for all http api requests.
     * You can't call this function directly, use the helper functions
     *
     * @see buy()
     * @see sell()
     * @see marketBuy()
     * @see marketSell() $this->httpRequest( "https://api.binance.com/api/v1/ticker/24hr");
     *
     * @param $url string the endpoint to query, typically includes query string
     * @param $method string this should be typically GET, POST or DELETE
     * @param $params array addtional options for the request
     * @param $signed bool true or false sign the request with api secret
     * @return array containing the response
     * @throws \Exception
     */
    private function httpRequest(string $url, string $method = "GET", array $params = [], bool $signed = false)
    {
        if (function_exists('curl_init') === false) {
            throw new \Exception("Sorry cURL is not installed!");
        }

        if ($this->caOverride === false) {
            if (file_exists(getcwd() . '/ca.pem') === false) {
                $this->downloadCurlCaBundle();
            }
        }

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_VERBOSE, $this->httpDebug);
        $query = http_build_query($params, '', '&');

        // signed with params
        if ($signed === true) {
            if (empty($this->api_key)) {
                throw new \Exception("signedRequest error: API Key not set!");
            }

            if (empty($this->api_secret)) {
                throw new \Exception("signedRequest error: API Secret not set!");
            }

            $base = $this->base;
            $ts = (microtime(true) * 1000) + $this->info['timeOffset'];
            $params['timestamp'] = number_format($ts, 0, '.', '');
            if (isset($params['wapi'])) {
                unset($params['wapi']);
                $base = $this->wapi;
            }
            $query = http_build_query($params, '', '&');
            $signature = hash_hmac('sha256', $query, $this->api_secret);
            $endpoint = $base . $url . '?' . $query . '&signature=' . $signature;
            curl_setopt($curl, CURLOPT_URL, $endpoint);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'X-MBX-APIKEY: ' . $this->api_key,
            ));
        }
        // params so buildquery string and append to url
        else if (count($params) > 0) {
            curl_setopt($curl, CURLOPT_URL, $this->base . $url . '?' . $query);
        }
        // no params so just the base url
        else {
            curl_setopt($curl, CURLOPT_URL, $this->base . $url);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'X-MBX-APIKEY: ' . $this->api_key,
            ));
        }
        curl_setopt($curl, CURLOPT_USERAGENT, "User-Agent: Mozilla/4.0 (compatible; PHP Binance API)");
        // Post and postfields
        if ($method === "POST") {
            curl_setopt($curl, CURLOPT_POST, true);
            // curl_setopt($curlch, CURLOPT_POSTFIELDS, $query);
        }
        // Delete Method
        if ($method === "DELETE") {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        }

        // PUT Method
        if ($method === "PUT") {
            curl_setopt($curl, CURLOPT_PUT, true);
        }

        // proxy settings
        if (is_array($this->proxyConf)) {
            curl_setopt($curl, CURLOPT_PROXY, $this->getProxyUriString());
            if (isset($this->proxyConf['user']) && isset($this->proxyConf['pass'])) {
                curl_setopt($curl, CURLOPT_PROXYUSERPWD, $this->proxyConf['user'] . ':' . $this->proxyConf['pass']);
            }
        }
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        // headers will proceed the output, json_decode will fail below
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 60);

        // set user defined curl opts last for overriding
        foreach ($this->curlOpts as $key => $value) {
            curl_setopt($curl, constant($key), $value);
        }

        if ($this->caOverride === false) {
            if (file_exists(getcwd() . '/ca.pem') === false) {
                $this->downloadCurlCaBundle();
            }
        }

        $output = curl_exec($curl);
        // Check if any error occurred
        if (curl_errno($curl) > 0) {
            // should always output error, not only on httpdebug
            // not outputing errors, hides it from users and ends up with tickets on github
            echo 'Curl error: ' . curl_error($curl) . "\n";
            return [];
        }
        curl_close($curl);
        $json = json_decode($output, true);
        if (isset($json['msg'])) {
            // should always output error, not only on httpdebug
            // not outputing errors, hides it from users and ends up with tickets on github
            echo "signedRequest error: {$output}" . PHP_EOL;
        }
        $this->transfered += strlen($output);
        $this->requestCount++;
        return $json;
    }

    /**
     * order formats the orders before sending them to the curl wrapper function
     * You can call this function directly or use the helper functions
     *
     * @see buy()
     * @see sell()
     * @see marketBuy()
     * @see marketSell() $this->httpRequest( "https://api.binance.com/api/v1/ticker/24hr");
     *
     * @param $side string typically "BUY" or "SELL"
     * @param $symbol string to buy or sell
     * @param $quantity string in the order
     * @param $price string for the order
     * @param $type string is determined by the symbol bu typicall LIMIT, STOP_LOSS_LIMIT etc.
     * @param $flags array additional transaction options
     * @param $test bool whether to test or not, test only validates the query
     * @return array containing the response
     * @throws \Exception
     */
    public function order(string $side, string $symbol, $quantity, $price, string $type = "LIMIT", array $flags = [], bool $test = false)
    {
        $opt = [
            "symbol" => $symbol,
            "side" => $side,
            "type" => $type,
            "quantity" => $quantity,
            "recvWindow" => 60000,
        ];

        // someone has preformated there 8 decimal point double already
        // dont do anything, leave them do whatever they want
        if (gettype($price) !== "string") {
            // for every other type, lets format it appropriately
            $price = number_format($price, 8, '.', '');
        }

        if (is_numeric($quantity) === false) {
            // WPCS: XSS OK.
            echo "warning: quantity expected numeric got " . gettype($quantity) . PHP_EOL;
        }

        if (is_string($price) === false) {
            // WPCS: XSS OK.
            echo "warning: price expected string got " . gettype($price) . PHP_EOL;
        }

        if ($type === "LIMIT" || $type === "STOP_LOSS_LIMIT" || $type === "TAKE_PROFIT_LIMIT") {
            $opt["price"] = $price;
            $opt["timeInForce"] = "GTC";
        }

        if (isset($flags['stopPrice'])) {
            $opt['stopPrice'] = $flags['stopPrice'];
        }

        if (isset($flags['icebergQty'])) {
            $opt['icebergQty'] = $flags['icebergQty'];
        }

        if (isset($flags['newOrderRespType'])) {
            $opt['newOrderRespType'] = $flags['newOrderRespType'];
        }

        $qstring = ($test === false) ? "v3/order" : "v3/order/test";
        return $this->httpRequest($qstring, "POST", $opt, true);
    }

    /**
     * candlesticks get the candles for the given intervals
     * 1m,3m,5m,15m,30m,1h,2h,4h,6h,8h,12h,1d,3d,1w,1M
     *
     * $candles = $api->candlesticks("BNBBTC", "5m");
     *
     * @param $symbol string to query
     * @param $interval string to request
     * @param $limit int limit the amount of candles
     * @param $startTime string request candle information starting from here
     * @param $endTime string request candle information ending here
     * @return array containing the response
     * @throws \Exception
     */
    public function candlesticks(string $symbol, string $interval = "5m", int $limit = null, $startTime = null, $endTime = null)
    {
        if (!isset($this->charts[$symbol])) {
            $this->charts[$symbol] = [];
        }

        $opt = [
            "symbol" => $symbol,
            "interval" => $interval,
        ];

        if ($limit) {
            $opt["limit"] = $limit;
        }

        if ($startTime) {
            $opt["startTime"] = $startTime;
        }

        if ($endTime) {
            $opt["endTime"] = $endTime;
        }

        $response = $this->httpRequest("v1/klines", "GET", $opt);

        if (is_array($response) === false) {
            return [];
        }

        if (count($response) === 0) {
            echo "warning: v1/klines returned empty array, usually a blip in the connection or server" . PHP_EOL;
            return [];
        }

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
     * @param $array array of your balances
     * @param $priceData array of prices
     * @return array containing the response
     */
    private function balanceData(array $array, $priceData)
    {
        $balances = [];

        if (is_array($priceData)) {
            $btc_value = $btc_total = 0.00;
        }

        if (empty($array) || empty($array['balances'])) {
            // WPCS: XSS OK.
            echo "balanceData error: Please make sure your system time is synchronized: call \$api->useServerTime() before this function" . PHP_EOL;
            echo "ERROR: Invalid request. Please double check your API keys and permissions." . PHP_EOL;
            return [];
        }

        foreach ($array['balances'] as $obj) {
            $asset = $obj['asset'];
            $balances[$asset] = [
                "available" => $obj['free'],
                "onOrder" => $obj['locked'],
                "btcValue" => 0.00000000,
                "btcTotal" => 0.00000000,
            ];

            if (is_array($priceData) === false) {
                continue;
            }

            if ($obj['free'] + $obj['locked'] < 0.00000001) {
                continue;
            }

            if ($asset === 'BTC') {
                $balances[$asset]['btcValue'] = $obj['free'];
                $balances[$asset]['btcTotal'] = $obj['free'] + $obj['locked'];
                $btc_value += $obj['free'];
                $btc_total += $obj['free'] + $obj['locked'];
                continue;
            }

            $symbol = $asset . 'BTC';

            if ($symbol === 'BTCUSDT') {
                $btcValue = number_format($obj['free'] / $priceData['BTCUSDT'], 8, '.', '');
                $btcTotal = number_format(($obj['free'] + $obj['locked']) / $priceData['BTCUSDT'], 8, '.', '');
            } elseif (isset($priceData[$symbol]) === false) {
                $btcValue = $btcTotal = 0;
            } else {
                $btcValue = number_format($obj['free'] * $priceData[$symbol], 8, '.', '');
                $btcTotal = number_format(($obj['free'] + $obj['locked']) * $priceData[$symbol], 8, '.', '');
            }

            $balances[$asset]['btcValue'] = $btcValue;
            $balances[$asset]['btcTotal'] = $btcTotal;
            $btc_value += $btcValue;
            $btc_total += $btcTotal;
        }
        if (is_array($priceData)) {
            uasort($balances, function ($opA, $opB) {
                return $opA['btcValue'] < $opB['btcValue'];
            });
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
     * @param $json array data to convert
     * @return array
     */
    private function balanceHandler(array $json)
    {
        $balances = [];
        foreach ($json as $item) {
            $asset = $item->a;
            $available = $item->f;
            $onOrder = $item->l;
            $balances[$asset] = [
                "available" => $available,
                "onOrder" => $onOrder,
            ];
        }
        return $balances;
    }

    /**
     * tickerStreamHandler Convert WebSocket ticker data into array
     *
     * $data = $this->tickerStreamHandler( $json );
     *
     * @param $json object data to convert
     * @return array
     */
    private function tickerStreamHandler(\stdClass $json)
    {
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
            "numTrades" => $json->n,
        ];
    }

    /**
     * tickerStreamHandler Convert WebSocket trade execution into array
     *
     * $data = $this->executionHandler( $json );
     *
     * @param \stdClass $json object data to convert
     * @return array
     */
    private function executionHandler(\stdClass $json)
    {
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
            "eventTime" => $json->E,
        ];
    }

    /**
     * chartData Convert kline data into object
     *
     * $object = $this->chartData($symbol, $interval, $ticks);
     *
     * @param $symbol string of your currency
     * @param $interval string the time interval
     * @param $ticks array of the canbles array
     * @return array object of the chartdata
     */
    private function chartData(string $symbol, string $interval, array $ticks)
    {
        if (!isset($this->info[$symbol])) {
            $this->info[$symbol] = [];
        }

        if (!isset($this->info[$symbol][$interval])) {
            $this->info[$symbol][$interval] = [];
        }

        $output = [];
        foreach ($ticks as $tick) {
            list($openTime, $open, $high, $low, $close, $assetVolume, $closeTime, $baseVolume, $trades, $assetBuyVolume, $takerBuyVolume, $ignored) = $tick;
            $output[$openTime] = [
                "open" => $open,
                "high" => $high,
                "low" => $low,
                "close" => $close,
                "volume" => $baseVolume,
                "openTime" => $openTime,
                "closeTime" => $closeTime,
                "assetVolume" => $assetVolume,
                "baseVolume" => $baseVolume,
                "trades" => $trades,
                "assetBuyVolume" => $assetBuyVolume,
                "takerBuyVolume" => $takerBuyVolume,
                "ignored" => $ignored,
            ];
        }

        if (isset($openTime)) {
            $this->info[$symbol][$interval]['firstOpen'] = $openTime;
        }

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
    private function tradesData(array $trades)
    {
        $output = [];
        foreach ($trades as $trade) {
            $price = $trade['p'];
            $quantity = $trade['q'];
            $timestamp = $trade['T'];
            $maker = $trade['m'] ? 'true' : 'false';
            $output[] = [
                "price" => $price,
                "quantity" => $quantity,
                "timestamp" => $timestamp,
                "maker" => $maker,
            ];
        }
        return $output;
    }

    /**
     * bookPriceData Consolidates Book Prices into an easy to use object
     *
     * $bookPriceData = $this->bookPriceData($array);
     *
     * @param $array array book prices
     * @return array easier format for book prices information
     */
    private function bookPriceData(array $array)
    {
        $bookprices = [];
        foreach ($array as $obj) {
            $bookprices[$obj['symbol']] = [
                "bid" => $obj['bidPrice'],
                "bids" => $obj['bidQty'],
                "ask" => $obj['askPrice'],
                "asks" => $obj['askQty'],
            ];
        }
        return $bookprices;
    }

    /**
     * priceData Converts Price Data into an easy key/value array
     *
     * $array = $this->priceData($array);
     *
     * @param $array array of prices
     * @return array of key/value pairs
     */
    private function priceData(array $array)
    {
        $prices = [];
        foreach ($array as $obj) {
            $prices[$obj['symbol']] = $obj['price'];
        }
        return $prices;
    }

    /**
     * cumulative Converts depth cache into a cumulative array
     *
     * $cumulative = $api->cumulative($depth);
     *
     * @param $depth array cache array
     * @return array cumulative depth cache
     */
    public function cumulative(array $depth)
    {
        $bids = [];
        $asks = [];
        $cumulative = 0;
        foreach ($depth['bids'] as $price => $quantity) {
            $cumulative += $quantity;
            $bids[] = [
                $price,
                $cumulative,
            ];
        }
        $cumulative = 0;
        foreach ($depth['asks'] as $price => $quantity) {
            $cumulative += $quantity;
            $asks[] = [
                $price,
                $cumulative,
            ];
        }
        return [
            "bids" => $bids,
            "asks" => array_reverse($asks),
        ];
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
    public function highstock(array $chart, bool $include_volume = false)
    {
        $array = [];
        foreach ($chart as $timestamp => $obj) {
            $line = [
                $timestamp,
                floatval($obj['open']),
                floatval($obj['high']),
                floatval($obj['low']),
                floatval($obj['close']),
            ];
            if ($include_volume) {
                $line[] = floatval($obj['volume']);
            }

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
    public function first(array $array)
    {
        if (count($array) > 0) {
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
    public function last(array $array)
    {
        if (count($array) > 0) {
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
    public function displayDepth(array $array)
    {
        $output = '';
        foreach ([
            'asks',
            'bids',
        ] as $type) {
            $entries = $array[$type];
            if ($type === 'asks') {
                $entries = array_reverse($entries);
            }

            $output .= "{$type}:" . PHP_EOL;
            foreach ($entries as $price => $quantity) {
                $total = number_format($price * $quantity, 8, '.', '');
                $quantity = str_pad(str_pad(number_format(rtrim($quantity, '.0')), 10, ' ', STR_PAD_LEFT), 15);
                $output .= "{$price} {$quantity} {$total}" . PHP_EOL;
            }
            // echo str_repeat('-', 32).PHP_EOL;
        }
        return $output;
    }

    /**
     * depthData Formats depth data for nice display
     *
     * $array = $this->depthData($symbol, $json);
     *
     * @param $symbol string to display
     * @param $json array of the depth infomration
     * @return array of the depth information
     */
    private function depthData(string $symbol, array $json)
    {
        $bids = $asks = [];
        foreach ($json['bids'] as $obj) {
            $bids[$obj[0]] = $obj[1];
        }
        foreach ($json['asks'] as $obj) {
            $asks[$obj[0]] = $obj[1];
        }
        return $this->depthCache[$symbol] = [
            "bids" => $bids,
            "asks" => $asks,
        ];
    }
    
    /**
     * roundStep rounds quantity with stepSize
     * @param $qty quantity
     * @param $stepSize parameter from exchangeInfo
     * @return rounded value. example: roundStep(1.2345, 0.1) = 1.2
     *
     */
    public function roundStep($qty, $stepSize = 0.1) {
        $precision = strlen(substr(strrchr(rtrim($stepSize,'0'), '.'), 1));
        return round((($qty / $stepSize) | 0) * $stepSize, $precision);
    }
    
    /**
     * roundTicks rounds price with tickSize
     * @param $value price
     * @param $tickSize parameter from exchangeInfo
     * @return rounded value. example: roundStep(1.2345, 0.1) = 1.2
     *
     */
    public function roundTicks($price, $tickSize) {
        $precision = strlen(rtrim(substr($tickSize,strpos($tickSize, '.', 1) + 1), '0'));
        return number_format($price, $precision, '.', '');
    }
    
    /**
     * getTransfered gets the total transfered in b,Kb,Mb,Gb
     *
     * $transfered = $api->getTransfered();
     *
     * @return string showing the total transfered
     */
    public function getTransfered()
    {
        $base = log($this->transfered, 1024);
        $suffixes = array(
            '',
            'K',
            'M',
            'G',
            'T',
        );
        return round(pow(1024, $base - floor($base)), 2) . ' ' . $suffixes[floor($base)];
    }

    /**
     * getRequestCount gets the total number of API calls
     *
     * $apiCount = $api->getRequestCount();
     *
     * @return int get the total number of api calls
     */
    public function getRequestCount()
    {
        return $this->requestCount;
    }

    /**
     * addToTransfered add interger bytes to the total transfered
     * also incrementes the api counter
     *
     * $apiCount = $api->addToTransfered( $int );
     *
     * @return null
     */
    public function addToTransfered(int $int)
    {
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
     * @param $json array of depth bids and asks
     * @return null
     */
    private function depthHandler(array $json)
    {
        $symbol = $json['s'];
        if ($json['u'] <= $this->info[$symbol]['firstUpdate']) {
            return;
        }

        foreach ($json['b'] as $bid) {
            $this->depthCache[$symbol]['bids'][$bid[0]] = $bid[1];
            if ($bid[1] == "0.00000000") {
                unset($this->depthCache[$symbol]['bids'][$bid[0]]);
            }

        }
        foreach ($json['a'] as $ask) {
            $this->depthCache[$symbol]['asks'][$ask[0]] = $ask[1];
            if ($ask[1] == "0.00000000") {
                unset($this->depthCache[$symbol]['asks'][$ask[0]]);
            }

        }
    }

    /**
     * chartHandler For WebSocket Chart Cache
     *
     * $this->chartHandler($symbol, $interval, $json);
     *
     * @param $symbol string to sort
     * @param $interval string time
     * @param \stdClass $json object time
     * @return null
     */
    private function chartHandler(string $symbol, string $interval, \stdClass $json)
    {
        if (!$this->info[$symbol][$interval]['firstOpen']) { // Wait for /kline to finish loading
            $this->chartQueue[$symbol][$interval][] = $json;
            return;
        }
        $chart = $json->k;
        $symbol = $json->s;
        $interval = $chart->i;
        $tick = $chart->t;
        if ($tick < $this->info[$symbol][$interval]['firstOpen']) {
            return;
        }
        // Filter out of sync data
        $open = $chart->o;
        $high = $chart->h;
        $low = $chart->l;
        $close = $chart->c;
        $volume = $chart->q; // +trades buyVolume assetVolume makerVolume
        $this->charts[$symbol][$interval][$tick] = [
            "open" => $open,
            "high" => $high,
            "low" => $low,
            "close" => $close,
            "volume" => $volume,
        ];
    }

    /**
     * sortDepth Sorts depth data for display & getting highest bid and lowest ask
     *
     * $sorted = $api->sortDepth($symbol, $limit);
     *
     * @param $symbol string to sort
     * @param $limit int depth
     * @return null
     */
    public function sortDepth(string $symbol, int $limit = 11)
    {
        $bids = $this->depthCache[$symbol]['bids'];
        $asks = $this->depthCache[$symbol]['asks'];
        krsort($bids);
        ksort($asks);
        return [
            "asks" => array_slice($asks, 0, $limit, true),
            "bids" => array_slice($bids, 0, $limit, true),
        ];
    }

    /**
     * depthCache Pulls /depth data and subscribes to @depth WebSocket endpoint
     * Maintains a local Depth Cache in sync via lastUpdateId.
     * See depth() and depthHandler()
     *
     * $api->depthCache(["BNBBTC"], function($api, $symbol, $depth) {
     * echo "{$symbol} depth cache update".PHP_EOL;
     * //print_r($depth); // Print all depth data
     * $limit = 11; // Show only the closest asks/bids
     * $sorted = $api->sortDepth($symbol, $limit);
     * $bid = $api->first($sorted['bids']);
     * $ask = $api->first($sorted['asks']);
     * echo $api->displayDepth($sorted);
     * echo "ask: {$ask}".PHP_EOL;
     * echo "bid: {$bid}".PHP_EOL;
     * });
     *
     * @param $symbol string optional array of symbols
     * @param $callback callable closure
     * @return null
     */
    public function depthCache($symbols, callable $callback)
    {
        if (!is_array($symbols)) {
            $symbols = [
                $symbols,
            ];
        }

        $loop = \React\EventLoop\Factory::create();
        $react = new \React\Socket\Connector($loop);
        $connector = new \Ratchet\Client\Connector($loop, $react);
        foreach ($symbols as $symbol) {
            if (!isset($this->info[$symbol])) {
                $this->info[$symbol] = [];
            }

            if (!isset($this->depthQueue[$symbol])) {
                $this->depthQueue[$symbol] = [];
            }

            if (!isset($this->depthCache[$symbol])) {
                $this->depthCache[$symbol] = [
                    "bids" => [],
                    "asks" => [],
                ];
            }

            $this->info[$symbol]['firstUpdate'] = 0;
            $endpoint = strtolower($symbol) . '@depthCache';
            $this->subscriptions[$endpoint] = true;

            $connector($this->stream . strtolower($symbol) . '@depth')->then(function ($ws) use ($callback, $symbol, $loop, $endpoint) {
                $ws->on('message', function ($data) use ($ws, $callback, $loop, $endpoint) {
                    if ($this->subscriptions[$endpoint] === false) {
                        //$this->subscriptions[$endpoint] = null;
                        $loop->stop();
                        return; //return $ws->close();
                    }
                    $json = json_decode($data, true);
                    $symbol = $json['s'];
                    if (intval($this->info[$symbol]['firstUpdate']) === 0) {
                        $this->depthQueue[$symbol][] = $json;
                        return;
                    }
                    $this->depthHandler($json);
                    call_user_func($callback, $this, $symbol, $this->depthCache[$symbol]);
                });
                $ws->on('close', function ($code = null, $reason = null) use ($symbol, $loop) {
                    // WPCS: XSS OK.
                    echo "depthCache({$symbol}) WebSocket Connection closed! ({$code} - {$reason})" . PHP_EOL;
                    $loop->stop();
                });
            }, function ($e) use ($loop, $symbol) {
                // WPCS: XSS OK.
                echo "depthCache({$symbol})) Could not connect: {$e->getMessage()}" . PHP_EOL;
                $loop->stop();
            });
            $this->depth($symbol);
            foreach ($this->depthQueue[$symbol] as $data) {
                //TODO:: WTF ??? where is json and what should be in it ??
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
     * echo "{$symbol} trades update".PHP_EOL;
     * print_r($trades);
     * });
     *
     * @param $symbols
     * @param $callback callable closure
     * @return null
     */
    public function trades($symbols, callable $callback)
    {
        if (!is_array($symbols)) {
            $symbols = [
                $symbols,
            ];
        }

        $loop = \React\EventLoop\Factory::create();
        $react = new \React\Socket\Connector($loop);
        $connector = new \Ratchet\Client\Connector($loop, $react);
        foreach ($symbols as $symbol) {
            if (!isset($this->info[$symbol])) {
                $this->info[$symbol] = [];
            }

            // $this->info[$symbol]['tradesCallback'] = $callback;

            $endpoint = strtolower($symbol) . '@trades';
            $this->subscriptions[$endpoint] = true;

            $connector($this->stream . strtolower($symbol) . '@aggTrade')->then(function ($ws) use ($callback, $symbol, $loop, $endpoint) {
                $ws->on('message', function ($data) use ($ws, $callback, $loop, $endpoint) {
                    if ($this->subscriptions[$endpoint] === false) {
                        //$this->subscriptions[$endpoint] = null;
                        $loop->stop();
                        return; //return $ws->close();
                    }
                    $json = json_decode($data, true);
                    $symbol = $json['s'];
                    $price = $json['p'];
                    $quantity = $json['q'];
                    $timestamp = $json['T'];
                    $maker = $json['m'] ? 'true' : 'false';
                    $trades = [
                        "price" => $price,
                        "quantity" => $quantity,
                        "timestamp" => $timestamp,
                        "maker" => $maker,
                    ];
                    // $this->info[$symbol]['tradesCallback']($this, $symbol, $trades);
                    call_user_func($callback, $this, $symbol, $trades);
                });
                $ws->on('close', function ($code = null, $reason = null) use ($symbol, $loop) {
                    // WPCS: XSS OK.
                    echo "trades({$symbol}) WebSocket Connection closed! ({$code} - {$reason})" . PHP_EOL;
                    $loop->stop();
                });
            }, function ($e) use ($loop, $symbol) {
                // WPCS: XSS OK.
                echo "trades({$symbol}) Could not connect: {$e->getMessage()}" . PHP_EOL;
                $loop->stop();
            });
        }
        $loop->run();
    }

    /**
     * ticker pulls 24h price change statistics via WebSocket
     *
     * $api->ticker(false, function($api, $symbol, $ticker) {
     * print_r($ticker);
     * });
     *
     * @param $symbol string optional symbol or false
     * @param $callback callable closure
     * @return null
     */
    public function ticker($symbol, callable $callback)
    {
        $endpoint = $symbol ? strtolower($symbol) . '@ticker' : '!ticker@arr';
        $this->subscriptions[$endpoint] = true;

        // @codeCoverageIgnoreStart
        // phpunit can't cover async function
        \Ratchet\Client\connect($this->stream . $endpoint)->then(function ($ws) use ($callback, $symbol, $endpoint) {
            $ws->on('message', function ($data) use ($ws, $callback, $symbol, $endpoint) {
                if ($this->subscriptions[$endpoint] === false) {
                    //$this->subscriptions[$endpoint] = null;
                    $ws->close();
                    return; //return $ws->close();
                }
                $json = json_decode($data);
                if ($symbol) {
                    call_user_func($callback, $this, $symbol, $this->tickerStreamHandler($json));
                } else {
                    foreach ($json as $obj) {
                        $return = $this->tickerStreamHandler($obj);
                        $symbol = $return['symbol'];
                        call_user_func($callback, $this, $symbol, $return);
                    }
                }
            });
            $ws->on('close', function ($code = null, $reason = null) {
                // WPCS: XSS OK.
                echo "ticker: WebSocket Connection closed! ({$code} - {$reason})" . PHP_EOL;
            });
        }, function ($e) {
            // WPCS: XSS OK.
            echo "ticker: Could not connect: {$e->getMessage()}" . PHP_EOL;
        });
        // @codeCoverageIgnoreEnd
    }

    /**
     * chart Pulls /kline data and subscribes to @klines WebSocket endpoint
     *
     * $api->chart(["BNBBTC"], "15m", function($api, $symbol, $chart) {
     * echo "{$symbol} chart update\n";
     * print_r($chart);
     * });
     *
     * @param $symbols string required symbols
     * @param $interval string time inteval
     * @param $callback callable closure
     * @return null
     * @throws \Exception
     */
    public function chart($symbols, string $interval = "30m", callable $callback)
    {
        if (!is_array($symbols)) {
            $symbols = [
                $symbols,
            ];
        }

        $loop = \React\EventLoop\Factory::create();
        $react = new \React\Socket\Connector($loop);
        $connector = new \Ratchet\Client\Connector($loop, $react);
        foreach ($symbols as $symbol) {
            if (!isset($this->charts[$symbol])) {
                $this->charts[$symbol] = [];
            }

            $this->charts[$symbol][$interval] = [];
            if (!isset($this->info[$symbol])) {
                $this->info[$symbol] = [];
            }

            if (!isset($this->info[$symbol][$interval])) {
                $this->info[$symbol][$interval] = [];
            }

            if (!isset($this->chartQueue[$symbol])) {
                $this->chartQueue[$symbol] = [];
            }

            $this->chartQueue[$symbol][$interval] = [];
            $this->info[$symbol][$interval]['firstOpen'] = 0;
            $endpoint = strtolower($symbol) . '@kline_' . $interval;
            $this->subscriptions[$endpoint] = true;
            $connector($this->stream . $endpoint)->then(function ($ws) use ($callback, $symbol, $loop, $endpoint, $interval) {
                $ws->on('message', function ($data) use ($ws, $loop, $callback, $endpoint) {
                    if ($this->subscriptions[$endpoint] === false) {
                        //$this->subscriptions[$endpoint] = null;
                        $loop->stop();
                        return; //return $ws->close();
                    }
                    $json = json_decode($data);
                    $chart = $json->k;
                    $symbol = $json->s;
                    $interval = $chart->i;
                    $this->chartHandler($symbol, $interval, $json);
                    call_user_func($callback, $this, $symbol, $this->charts[$symbol][$interval]);
                });
                $ws->on('close', function ($code = null, $reason = null) use ($symbol, $loop, $interval) {
                    // WPCS: XSS OK.
                    echo "chart({$symbol},{$interval}) WebSocket Connection closed! ({$code} - {$reason})" . PHP_EOL;
                    $loop->stop();
                });
            }, function ($e) use ($loop, $symbol, $interval) {
                // WPCS: XSS OK.
                echo "chart({$symbol},{$interval})) Could not connect: {$e->getMessage()}" . PHP_EOL;
                $loop->stop();
            });
            $this->candlesticks($symbol, $interval);
            foreach ($this->chartQueue[$symbol][$interval] as $json) {
                $this->chartHandler($symbol, $interval, $json);
            }
            $this->chartQueue[$symbol][$interval] = [];
            call_user_func($callback, $this, $symbol, $this->charts[$symbol][$interval]);
        }
        $loop->run();
    }

    /**
     * kline Subscribes to @klines WebSocket endpoint for latest chart data only
     *
     * $api->kline(["BNBBTC"], "15m", function($api, $symbol, $chart) {
     * echo "{$symbol} chart update\n";
     * print_r($chart);
     * });
     *
     * @param $symbols string required symbols
     * @param $interval string time inteval
     * @param $callback callable closure
     * @return null
     * @throws \Exception
     */
    public function kline($symbols, string $interval = "30m", callable $callback)
    {
        if (!is_array($symbols)) {
            $symbols = [
                $symbols,
            ];
        }

        $loop = \React\EventLoop\Factory::create();
        $react = new \React\Socket\Connector($loop);
        $connector = new \Ratchet\Client\Connector($loop, $react);
        foreach ($symbols as $symbol) {
            $endpoint = strtolower($symbol) . '@kline_' . $interval;
            $this->subscriptions[$endpoint] = true;
            $connector($this->stream . $endpoint)->then(function ($ws) use ($callback, $symbol, $loop, $endpoint, $interval) {
                $ws->on('message', function ($data) use ($ws, $loop, $callback, $endpoint) {
                    if ($this->subscriptions[$endpoint] === false) {
                        $loop->stop();
                        return;
                    }
                    $json = json_decode($data);
                    $chart = $json->k;
                    $symbol = $json->s;
                    $interval = $chart->i;
                    call_user_func($callback, $this, $symbol, $chart);
                });
                $ws->on('close', function ($code = null, $reason = null) use ($symbol, $loop, $interval) {
                    // WPCS: XSS OK.
                    echo "kline({$symbol},{$interval}) WebSocket Connection closed! ({$code} - {$reason})" . PHP_EOL;
                    $loop->stop();
                });
            }, function ($e) use ($loop, $symbol, $interval) {
                // WPCS: XSS OK.
                echo "kline({$symbol},{$interval})) Could not connect: {$e->getMessage()}" . PHP_EOL;
                $loop->stop();
            });
        }
        $loop->run();
    }

    /**
     * terminate Terminates websocket endpoints. View endpoints first: print_r($api->subscriptions)
     *
     * $api->terminate('ethbtc_kline@5m');
     *
     * @return null
     */
    public function terminate($endpoint)
    {
        // check if $this->subscriptions[$endpoint] is true otherwise error
        $this->subscriptions[$endpoint] = false;
    }

    /**
     * keepAlive Keep-alive function for userDataStream
     *
     * $api->keepAlive();
     *
     * @return null
     */
    public function keepAlive()
    {
        $loop = \React\EventLoop\Factory::create();
        $loop->addPeriodicTimer(30, function () {
            $listenKey = $this->listenKey;
            $this->httpRequest("v1/userDataStream?listenKey={$listenKey}", "PUT", []);
        });
        $loop->run();
    }

    /**
     * userData Issues userDataStream token and keepalive, subscribes to userData WebSocket
     *
     * $balance_update = function($api, $balances) {
     * print_r($balances);
     * echo "Balance update".PHP_EOL;
     * };
     *
     * $order_update = function($api, $report) {
     * echo "Order update".PHP_EOL;
     * print_r($report);
     * $price = $report['price'];
     * $quantity = $report['quantity'];
     * $symbol = $report['symbol'];
     * $side = $report['side'];
     * $orderType = $report['orderType'];
     * $orderId = $report['orderId'];
     * $orderStatus = $report['orderStatus'];
     * $executionType = $report['orderStatus'];
     * if( $executionType == "NEW" ) {
     * if( $executionType == "REJECTED" ) {
     * echo "Order Failed! Reason: {$report['rejectReason']}".PHP_EOL;
     * }
     * echo "{$symbol} {$side} {$orderType} ORDER #{$orderId} ({$orderStatus})".PHP_EOL;
     * echo "..price: {$price}, quantity: {$quantity}".PHP_EOL;
     * return;
     * }
     *
     * //NEW, CANCELED, REPLACED, REJECTED, TRADE, EXPIRED
     * echo "{$symbol} {$side} {$executionType} {$orderType} ORDER #{$orderId}".PHP_EOL;
     * };
     * $api->userData($balance_update, $order_update);
     *
     * @param $balance_callback callable function
     * @param bool $execution_callback callable function
     * @return null
     * @throws \Exception
     */
    public function userData(&$balance_callback, &$execution_callback = false)
    {
        $response = $this->httpRequest("v1/userDataStream", "POST", []);
        $this->listenKey = $response['listenKey'];
        $this->info['balanceCallback'] = $balance_callback;
        $this->info['executionCallback'] = $execution_callback;

        $this->subscriptions['@userdata'] = true;

        // @codeCoverageIgnoreStart
        // phpunit can't cover async function
        \Ratchet\Client\connect($this->stream . $this->listenKey)->then(function ($ws) {
            $ws->on('message', function ($data) use ($ws) {
                if ($this->subscriptions['@userdata'] === false) {
                    //$this->subscriptions[$endpoint] = null;
                    $ws->close();
                    return; //return $ws->close();
                }
                $json = json_decode($data);
                $type = $json->e;
                if ($type === "outboundAccountInfo") {
                    $balances = $this->balanceHandler($json->B);
                    $this->info['balanceCallback']($this, $balances);
                } elseif ($type === "executionReport") {
                    $report = $this->executionHandler($json);
                    if ($this->info['executionCallback']) {
                        $this->info['executionCallback']($this, $report);
                    }
                }
            });
            $ws->on('close', function ($code = null, $reason = null) {
                // WPCS: XSS OK.
                echo "userData: WebSocket Connection closed! ({$code} - {$reason})" . PHP_EOL;
            });
        }, function ($e) {
            // WPCS: XSS OK.
            echo "userData: Could not connect: {$e->getMessage()}" . PHP_EOL;
        });
        // @codeCoverageIgnoreEnd
    }

    /**
     * miniTicker Get miniTicker for all symbols
     *
     * $api->miniTicker(function($api, $ticker) {
     * print_r($ticker);
     * });
     *
     * @param $callback callable function closer that takes 2 arguments, $pai and $ticker data
     * @return null
     */
    public function miniTicker(callable $callback)
    {
        $endpoint = '@miniticker';
        $this->subscriptions[$endpoint] = true;

        // @codeCoverageIgnoreStart
        // phpunit can't cover async function
        \Ratchet\Client\connect($this->stream . '!miniTicker@arr')->then(function ($ws) use ($callback, $endpoint) {
            $ws->on('message', function ($data) use ($ws, $callback, $endpoint) {
                if ($this->subscriptions[$endpoint] === false) {
                    //$this->subscriptions[$endpoint] = null;
                    $ws->close();
                    return; //return $ws->close();
                }
                $json = json_decode($data, true);
                $markets = [];
                foreach ($json as $obj) {
                    $markets[] = [
                        "symbol" => $obj['s'],
                        "close" => $obj['c'],
                        "open" => $obj['o'],
                        "high" => $obj['h'],
                        "low" => $obj['l'],
                        "volume" => $obj['v'],
                        "quoteVolume" => $obj['q'],
                        "eventTime" => $obj['E'],
                    ];
                }
                call_user_func($callback, $this, $markets);
            });
            $ws->on('close', function ($code = null, $reason = null) {
                // WPCS: XSS OK.
                echo "miniticker: WebSocket Connection closed! ({$code} - {$reason})" . PHP_EOL;
            });
        }, function ($e) {
            // WPCS: XSS OK.
            echo "miniticker: Could not connect: {$e->getMessage()}" . PHP_EOL;
        });
        // @codeCoverageIgnoreEnd
    }

    /**
     * Due to ongoing issues with out of date wamp CA bundles
     * This function downloads ca bundle for curl website
     * and uses it as part of the curl options
     */
    private function downloadCurlCaBundle()
    {
        $output_filename = getcwd() . "/ca.pem";

        if (is_writable(getcwd()) === false) {
            die(getcwd() . " folder is not writeable, plese check your permissions");
        }

        $host = "https://curl.haxx.se/ca/cacert.pem";
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $host);
        curl_setopt($curl, CURLOPT_VERBOSE, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

        // proxy settings
        if (is_array($this->proxyConf)) {
            curl_setopt($curl, CURLOPT_PROXY, $this->getProxyUriString());
            if (isset($this->proxyConf['user']) && isset($this->proxyConf['pass'])) {
                curl_setopt($curl, CURLOPT_PROXYUSERPWD, $this->proxyConf['user'] . ':' . $this->proxyConf['pass']);
            }
        }

        $result = curl_exec($curl);
        curl_close($curl);

        if ($result === false) {
            echo "Unable to to download the CA bundle $host" . PHP_EOL;
            return;
        }

        $fp = fopen($output_filename, 'w');

        if ($fp === false) {
            echo "Unable to write $output_filename, please check permissions on folder" . PHP_EOL;
            return;
        }

        fwrite($fp, $result);
        fclose($fp);
    }
}
