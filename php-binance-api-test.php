<?php
/*
 * ============================================================
 * php-binance-api
 * https://github.com/jaggedsoft/php-binance-api
 * ============================================================
 * Copyright 2017-, Jon Eyrick
 * Released under the MIT License
 * ============================================================
 */
declare (strict_types = 1)
;

require 'php-binance-api.php';
require 'vendor/autoload.php';

use PHPUnit\Framework\TestCase;

final class BinanceTest extends TestCase
{
    private $_testable = null;
    private static $config_file = null;
    private static $apikey = null;
    private static $apisecret = null;

    private static function writeConfig()
    {
        self::$apikey = "z5RQZ9n8JcS3HLDQmPpfLQIGGQN6TTs5pCP5CTnn4nYk2ImFcew49v4ZrmP3MGl5";
        self::$apisecret = "ZqePF1DcLb6Oa0CfcLWH0Tva59y8qBBIqu789JEY27jq0RkOKXpNl9992By1PN9Z";
        self::$config_file = getenv("HOME") . "/.config/jaggedsoft/php-binance-api.json";

        @unlink(self::$config_file);
        @file_put_contents(self::$config_file, "{ \"api-key\": \"" . self::$apikey . "\", \"api-secret\": \"" . self::$apisecret . "\" }");
    }

    private static function writeConfigWithProxy()
    {
        self::writeConfig();

        $contents = json_decode(file_get_contents(self::$config_file), true);
        $contents['proto'] = "https";
        $contents['user'] = "a";
        $contents['pass'] = "b";
        $contents['address'] = "1.2.3.4";
        $contents['port'] = "5678";

        @unlink(self::$config_file);
        @file_put_contents(self::$config_file, json_encode($contents));
    }

    private static function debug($pipe, $method, $msg)
    {
        $pipe = ($pipe == 0) ? STDOUT : STDERR;
        fwrite($pipe, $method . ": " . $msg . "\n");
    }

    public static function setUpBeforeClass()
    {
        self::debug(0, __METHOD__, "");
        self::writeConfig();
        if (file_exists(self::$config_file) == false) {
            self::debug(0, __METHOD__, self::$config_file . " not found");
            exit();
        }
    }

    protected function setUp()
    {
        self::debug(0, __METHOD__, "");
        self::writeConfig();
        $this->_testable = new Binance\API();
        $this->assertInstanceOf('Binance\API', $this->_testable);
    }

    public function testInstantiate0()
    {
        self::debug(0, __METHOD__, "");
        $this->_testable = new Binance\API();
        $this->assertInstanceOf('Binance\API', $this->_testable);
        $this->assertTrue(strcmp($this->_testable->api_key, self::$apikey) === 0);
        $this->assertTrue(strcmp($this->_testable->api_secret, self::$apisecret) === 0);
    }

    public function testInstantiate1()
    {
        self::debug(0, __METHOD__, "");
        $this->_testable = new Binance\API(self::$config_file);
        $this->assertInstanceOf('Binance\API', $this->_testable);
        $this->assertTrue(strcmp($this->_testable->api_key, self::$apikey) === 0);
        $this->assertTrue(strcmp($this->_testable->api_secret, self::$apisecret) === 0);
    }

    public function testInstantiate2()
    {
        self::debug(0, __METHOD__, "");
        $this->_testable = new Binance\API(self::$apikey, self::$apisecret);
        $this->assertInstanceOf('Binance\API', $this->_testable);
        $this->assertTrue(strcmp($this->_testable->api_key, self::$apikey) === 0);
        $this->assertTrue(strcmp($this->_testable->api_secret, self::$apisecret) === 0);
    }

    public function testInstantiate3()
    {
        self::debug(0, __METHOD__, "");
        $opts = ['CURLOPT_CERTINFO' => 1];
        $this->_testable = new Binance\API(self::$apikey, self::$apisecret, ["useServerTime" => true, "curlOpts" => $opts]);
        $this->assertInstanceOf('Binance\API', $this->_testable);
        $this->assertTrue(strcmp($this->_testable->api_key, self::$apikey) === 0);
        $this->assertTrue(strcmp($this->_testable->api_secret, self::$apisecret) === 0);
        $this->assertTrue($this->_testable->curlOpts['CURLOPT_CERTINFO'] === 1);
        $this->assertTrue($this->_testable->curlOpts['CURLOPT_CERTINFO'] === 1);
    }

    public function testInstantiate4()
    {
        self::debug(0, __METHOD__, "");
        $proxyconf['proto'] = "https";
        $proxyconf['user'] = "a";
        $proxyconf['pass'] = "b";
        $proxyconf['address'] = "1.2.3.4";
        $proxyconf['port'] = "5678";
        $this->_testable = new Binance\API(null, null, ["useServerTime" => true, "curlOpts" => array()], $proxyconf);
        $this->assertInstanceOf('Binance\API', $this->_testable);
        $this->assertTrue(strcmp($this->_testable->api_key, self::$apikey) === 0);
        $this->assertTrue(strcmp($this->_testable->api_secret, self::$apisecret) === 0);
        $this->assertTrue(strcmp($this->_testable->proxyConf['proto'], $proxyconf['proto']) === 0);
        $this->assertTrue(strcmp($this->_testable->proxyConf['user'], $proxyconf['user']) === 0);
        $this->assertTrue(strcmp($this->_testable->proxyConf['pass'], $proxyconf['pass']) === 0);
        $this->assertTrue(strcmp($this->_testable->proxyConf['address'], $proxyconf['address']) === 0);
        $this->assertTrue(strcmp($this->_testable->proxyConf['port'], $proxyconf['port']) === 0);
    }

    public function testInstantiate4CredentialsProxyOrdering()
    {
        self::debug(0, __METHOD__, "");
        $proxyconf['proto'] = "https";
        $proxyconf['user'] = "a";
        $proxyconf['pass'] = "b";
        $proxyconf['address'] = "1.2.3.4";
        $proxyconf['port'] = "5678";
        $this->_testable = new Binance\API(self::$apikey, self::$apisecret, ["useServerTime" => true, "curlOpts" => array()], $proxyconf);
        $this->assertInstanceOf('Binance\API', $this->_testable);
    }

    public function testMagicGet()
    {
        self::debug(0, __METHOD__, "");
        $this->assertTrue($this->_testable->DoesntExist === null);
    }

    public function testMagicSet()
    {
        self::debug(0, __METHOD__, "");
        $this->_testable->btc_total = 0.01;
        $this->assertTrue($this->_testable->btc_total === 0.01);
    }

    public function testAccount()
    {
        self::debug(0, __METHOD__, "");
        $details = $this->_testable->account();
        $check_keys = array(
            'makerCommission',
            'takerCommission',
            'buyerCommission',
            'sellerCommission',
            'canTrade',
            'canWithdraw',
            'canDeposit',
            'updateTime',
            'balances',
        );

        foreach ($check_keys as $check_key) {
            $this->assertTrue(isset($details[$check_key]));
            if (isset($details[$check_key]) == false) {
                self::debug(0, __METHOD__, "exchange info error: $check_key missing");
            }
        }

        $this->assertTrue(count($details['balances']) > 0);
    }

    public function testBuy()
    {
        self::debug(0, __METHOD__, "");
        $result = $this->_testable->buy("TRXBTC", "5", "0.001");
        $this->assertTrue(isset($result['code']));

        if (isset($result['code'])) {
            $this->assertTrue($result['code'] == "-2010");
        }

        if (isset($result['code']) == false) {
            self::debug(0, __METHOD__, "shouldn't be any money in the account");
        }
    }

    public function testBuyTest()
    {
        self::debug(0, __METHOD__, "");

        $symbol = "TRXBTC";

        $result = $this->_testable->buyTest($symbol, 999999, 0.00000001);
        $this->assertFalse(isset($result['code']));

        $result = $this->_testable->buyTest($symbol, strval("999999"), strval("0.00000001"));
        $this->assertFalse(isset($result['code']));

        $result = $this->_testable->buyTest($symbol, doubleval(999999), doubleval(0.00000001));
        $this->assertFalse(isset($result['code']));

        $result = $this->_testable->buyTest($symbol, floatval(999999), floatval(0.00000001));
        $this->assertFalse(isset($result['code']));
    }

    public function testSell()
    {
        self::debug(0, __METHOD__, "");
        $result = $this->_testable->sell("TRXBTC", "5", "0.001");
        $this->assertTrue(isset($result['code']));

        if (isset($result['code'])) {
            $this->assertTrue($result['code'] == "-2010");
        }

        if (isset($result['code']) == false) {
            self::debug(0, __METHOD__, "shouldn't be any money in the account");
        }
    }

    public function testSellTest()
    {
        self::debug(0, __METHOD__, "");

        $symbol = "TRXBTC";

        $result = $this->_testable->sellTest($symbol, 999999, 0.00000001);
        $this->assertFalse(isset($result['code']));

        $result = $this->_testable->sellTest($symbol, strval("999999"), strval("0.00000001"));
        $this->assertFalse(isset($result['code']));

        $result = $this->_testable->sellTest($symbol, doubleval(999999), doubleval(0.00000001));
        $this->assertFalse(isset($result['code']));

        $result = $this->_testable->sellTest($symbol, floatval(999999), floatval(0.00000001));
        $this->assertFalse(isset($result['code']));
    }

    public function testMarketBuy()
    {
        self::debug(0, __METHOD__, "");

        $symbol = "TRXBTC";

        $result = $this->_testable->marketBuy($symbol, 999999);
        $this->assertTrue(isset($result['code']));

        $result = $this->_testable->marketBuy($symbol, strval("999999"));
        $this->assertTrue(isset($result['code']));

        $result = $this->_testable->marketBuy($symbol, doubleval(999999));
        $this->assertTrue(isset($result['code']));

        $result = $this->_testable->marketBuy($symbol, floatval(999999));
        $this->assertTrue(isset($result['code']));
    }

    public function testMarketBuyTest()
    {
        self::debug(0, __METHOD__, "");
        $result = $this->_testable->marketBuyTest("TRXBTC", "5");
        $this->assertTrue((isset($result['code']) == false));

        if (isset($result['code'])) {
            self::debug(0, __METHOD__, "market buy error: " . $result['code'] . ":" . $result['msg']);
        }
    }

    public function testMarketSell()
    {
        self::debug(0, __METHOD__, "");
        $result = $this->_testable->marketSell("TRXBTC", "5");
        $this->assertTrue(isset($result['code']));

        if (isset($result['code'])) {
            $this->assertTrue($result['code'] == "-2010");
        }

        if (isset($result['code']) == false) {
            self::debug(0, __METHOD__, "shouldn't be any code in the account");
        }
    }

    public function testMarketSellTest()
    {
        self::debug(0, __METHOD__, "");
        $result = $this->_testable->marketSellTest("TRXBTC", "5");
        $this->assertTrue((isset($result['code']) == false));

        if (isset($result['code'])) {
            self::debug(0, __METHOD__, "market sell error: " . $result['code'] . ":" . $result['msg']);
        }
    }

    public function testCancel()
    {
        self::debug(0, __METHOD__, "");
        $result = $this->_testable->cancel("TRXBTC", "55555555");
        $this->assertTrue(isset($result['code']));

        if (isset($result['code'])) {
            $this->assertTrue($result['code'] == "-2011");
        }

        if (isset($result['code']) == false) {
            self::debug(0, __METHOD__, "shouldn't be anything to cancel");
        }
    }

    public function testOrderStatus()
    {
        self::debug(0, __METHOD__, "");
        $result = $this->_testable->orderStatus("TRXBTC", "55555555");
        $this->assertTrue(isset($result['code']));

        if (isset($result['code'])) {
            $this->assertTrue($result['code'] == "-2013");
        }

        if (isset($result['code']) == false) {
            self::debug(0, __METHOD__, "shouldn't be any order with this id");
        }
    }

    public function testOpenOrders()
    {
        self::debug(0, __METHOD__, "");
        $result = $this->_testable->openOrders("TRXBTC");
        $this->assertTrue((isset($result['code']) == false));
        $this->assertTrue(is_array($result));

        if (isset($result['code'])) {
            self::debug(0, __METHOD__, "open orders error: " . $result['code'] . ":" . $result['msg']);
        }
    }

    public function testOrders()
    {
        self::debug(0, __METHOD__, "");
        $result = $this->_testable->orders("TRXBTC");
        $this->assertTrue((isset($result['code']) == false));
        $this->assertTrue(is_array($result));

        if (isset($result['code'])) {
            self::debug(0, __METHOD__, "orders error: " . $result['code'] . ":" . $result['msg']);
        }
    }

    public function testHistory()
    {
        self::debug(0, __METHOD__, "");
        $result = $this->_testable->history("TRXBTC");
        $this->assertTrue((isset($result['code']) == false));
        $this->assertTrue(is_array($result));

        if (isset($result['code'])) {
            self::debug(0, __METHOD__, "history error: " . $result['code'] . ":" . $result['msg']);
        }
    }

    public function testUseServerTime()
    {
        self::debug(0, __METHOD__, "");
        $this->_testable->useServerTime();
        $this->assertTrue(true);
    }

    public function testTime()
    {
        self::debug(0, __METHOD__, "");
        $result = $this->_testable->time();
        $this->assertTrue((isset($result['code']) == false));
        $this->assertTrue(isset($result['serverTime']));

        if (isset($result['code'])) {
            self::debug(0, __METHOD__, "server time error: " . $result['code'] . ":" . $result['msg']);
        }
    }

    public function testExchangeInfo()
    {
        self::debug(0, __METHOD__, "");
        $result = $this->_testable->exchangeInfo();
        $this->assertTrue((isset($result['code']) == false));
        $check_keys = array(
            'timezone',
            'serverTime',
            'rateLimits',
            'exchangeFilters',
            'symbols',
        );

        foreach ($check_keys as $check_key) {
            $this->assertTrue(isset($result[$check_key]));
            if (isset($result[$check_key]) == false) {
                self::debug(0, __METHOD__, "exchange info error: $check_key missing\n");
            }
        }

        $this->assertTrue(count($result['symbols']) > 0);
        $this->assertTrue(count($result['rateLimits']) > 0);

        if (isset($result['code'])) {
            self::debug(0, __METHOD__, "exchange info error: " . $result['code'] . ":" . $result['msg']);
        }
    }

    public function testWithdraw()
    {
        self::debug(0, __METHOD__, "");
        $asset = "BTC";
        $address = "1C5gqLRs96Xq4V2ZZAR1347yUCpHie7sa";
        $amount = 0.2;
        $result = $this->_testable->withdraw($asset, $address, $amount);
        $this->assertTrue(true);
        if (isset($result['code'])) {
            self::debug(0, __METHOD__, "withdraw error: " . $result['code'] . ":" . $result['msg']);
        }
    }

    public function testDepositAddress()
    {
        self::debug(0, __METHOD__, "");
        $asset = "BTC";
        $result = $this->_testable->depositAddress($asset);
        $this->assertTrue(true);
        if (isset($result['code'])) {
            self::debug(0, __METHOD__, "depsoit error: " . $result['code'] . ":" . $result['msg']);
        }
    }

    public function testDepositHistory()
    {
        self::debug(0, __METHOD__, "");
        $asset = "BTC";
        $result = $this->_testable->depositHistory();
        $this->assertTrue(true);
        if (isset($result['code'])) {
            self::debug(0, __METHOD__, "deposit history error: " . $result['code'] . ":" . $result['msg']);
        }
    }

    public function testWithdrawHistory()
    {
        self::debug(0, __METHOD__, "");
        $asset = "BTC";
        $result = $this->_testable->withdrawHistory();
        $this->assertTrue(true);
        if (isset($result['code'])) {
            self::debug(0, __METHOD__, "withdraw history error: " . $result['code'] . ":" . $result['msg']);
        }
    }

    public function testPrices()
    {
        self::debug(0, __METHOD__, "");
        $result = $this->_testable->prices();
        $this->assertTrue((isset($result['code']) == false));
        $this->assertTrue(count($result) > 0);

        if (isset($result['code'])) {
            self::debug(0, __METHOD__, "prices error: " . $result['code'] . ":" . $result['msg']);
        }
    }

    public function testBookPrices()
    {
        self::debug(0, __METHOD__, "");
        $result = $this->_testable->bookPrices();
        $this->assertTrue((isset($result['code']) == false));
        $this->assertTrue(count($result) > 0);

        if (isset($result['code'])) {
            self::debug(0, __METHOD__, "book prices error: " . $result['code'] . ":" . $result['msg']);
        }
    }

    public function testPrevDay()
    {
        self::debug(0, __METHOD__, "");
        $result = $this->_testable->prevDay("TRXBTC");
        $this->assertTrue((isset($result['code']) == false));

        if (isset($result['code'])) {
            self::debug(0, __METHOD__, "previous day error: " . $result['code'] . ":" . $result['msg']);
        }
    }

    public function testAggTrades()
    {
        self::debug(0, __METHOD__, "");
        $result = $this->_testable->aggTrades("TRXBTC");
        $this->assertTrue((isset($result['code']) == false));
        $this->assertTrue(count($result) > 0);

        if (isset($result['code'])) {
            self::debug(0, __METHOD__, "agg trades error: " . $result['code'] . ":" . $result['msg']);
        }
    }

    public function testDepth()
    {
        self::debug(0, __METHOD__, "");
        $result = $this->_testable->depth("TRXBTC");
        $this->assertTrue((isset($result['code']) == false));
        $this->assertTrue(isset($result['bids']));
        $this->assertTrue(isset($result['asks']));

        if (isset($result['code'])) {
            self::debug(0, __METHOD__, "depth error: " . $result['code'] . ":" . $result['msg']);
        }
    }

    public function testBalances()
    {
        self::debug(0, __METHOD__, "");
        $result = $this->_testable->balances();
        $this->assertTrue((isset($result['code']) == false));
        $this->assertTrue(is_array($result));

        if (isset($result['code'])) {
            self::debug(0, __METHOD__, "balances error: " . $result['code'] . ":" . $result['msg']);
        }
    }

    public function testGetProxyUriString()
    {
        self::debug(0, __METHOD__, "");

        $proxyConf = [
            'proto' => 'http',
            'address' => '192.168.1.1',
            'port' => '8080',
            'user' => 'dude',
            'pass' => 'd00d',
        ];

        $this->_testable->setProxy($proxyConf);
        $uri = $this->_testable->getProxyUriString();
        $this->assertTrue($uri == $proxyConf['proto'] . "://" . $proxyConf['address'] . ":" . $proxyConf['port']);
    }

    public function testGetProxyUriStringFromFile()
    {
        self::debug(0, __METHOD__, "");
        self::writeConfigWithProxy();

        $this->_testable = new Binance\API();
        $this->assertInstanceOf('Binance\API', $this->_testable);
        $uri = $this->_testable->getProxyUriString();
        $this->assertTrue(strcmp($uri, "https://1.2.3.4:5678") == 0);
    }

    public function testHttpRequest()
    {
        self::debug(0, __METHOD__, "");
        $this->assertTrue(true);
    }

    public function testOrder()
    {
        self::debug(0, __METHOD__, "");
        $this->assertTrue(true);
    }

    public function testCandlesticks()
    {
        self::debug(0, __METHOD__, "");
        $result = $this->_testable->candlesticks("BNBBTC", "5m");
        $this->assertTrue(is_array($result));

        if (isset($result['code'])) {
            self::debug(0, __METHOD__, "candlesticks error: " . $result['code'] . ":" . $result['msg']);
        }
    }

    public function testBalanceData()
    {
        self::debug(0, __METHOD__, "");
        $ticker = $this->_testable->prices();
        $result = $this->_testable->balances($ticker);
        $this->assertTrue(is_array($result));

        $result = $this->_testable->balances();
        $this->assertTrue(is_array($result));

        if (isset($result['code'])) {
            self::debug(0, __METHOD__, "balances error: " . $result['code'] . ":" . $result['msg']);
        }
    }

    public function testBalanceHandler()
    {
        self::debug(0, __METHOD__, "");

        $jsonobj = json_decode(json_encode(array(
            "a" => 1,
            "f" => 2,
            "l" => 3,
        )));

        $arr = array();
        $arr[] = $jsonobj;
        $arr[] = $jsonobj;

        $newArr = $this->invokeMethod($this->_testable, 'balanceHandler', array($arr));

        $this->assertTrue(is_array($newArr));
    }

    public function testTickerStreamHandler()
    {
        self::debug(0, __METHOD__, "");

        $arr = array(
            "e" => 1,
            "E" => 2,
            "s" => 3,
            "p" => 4,
            "P" => 5,
            "w" => 6,
            "x" => 7,
            "c" => 8,
            "Q" => 9,
            "b" => 10,
            "B" => 11,
            "a" => 12,
            "A" => 13,
            "o" => 14,
            "h" => 15,
            "l" => 16,
            "v" => 17,
            "q" => 18,
            "O" => 19,
            "C" => 20,
            "F" => 21,
            "L" => 22,
            "n" => 23,
        );

        $newArr = $this->invokeMethod($this->_testable, 'tickerStreamHandler', array(
            json_decode(json_encode($arr)),
        ));

        $this->assertTrue(is_array($newArr));

        $indexs = array(
            "eventType",
            "eventTime",
            "symbol",
            "priceChange",
            "percentChange",
            "averagePrice",
            "prevClose",
            "close",
            "closeQty",
            "bestBid",
            "bestBidQty",
            "bestAsk",
            "bestAskQty",
            "open",
            "high",
            "low",
            "volume",
            "quoteVolume",
            "openTime",
            "closeTime",
            "firstTradeId",
            "lastTradeId",
            "numTrades",
        );

        foreach ($indexs as $index) {
            $this->assertTrue(isset($newArr[$index]));
        }
    }

    public function testExecutionHandler()
    {
        self::debug(0, __METHOD__, "");

        $arr = array(
            "s" => 1,
            "S" => 2,
            "o" => 3,
            "q" => 4,
            "p" => 5,
            "x" => 6,
            "X" => 7,
            "r" => 8,
            "i" => 9,
            "c" => 10,
            "T" => 11,
            "E" => 12,
        );

        $newArr = $this->invokeMethod($this->_testable, 'executionHandler', array(
            json_decode(json_encode($arr)),
        ));

        $this->assertTrue(is_array($newArr));

        $indexs = array(
            "symbol",
            "side",
            "orderType",
            "quantity",
            "price",
            "executionType",
            "orderStatus",
            "rejectReason",
            "orderId",
            "clientOrderId",
            "orderTime",
            "eventTime",
        );

        foreach ($indexs as $index) {
            $this->assertTrue(isset($newArr[$index]));
        }
    }

    public function testChartData()
    {
        self::debug(0, __METHOD__, "");
        $this->assertTrue(true);
    }

    public function testBookPriceData()
    {
        self::debug(0, __METHOD__, "");
        $this->assertTrue(true);
    }

    public function testPriceData()
    {
        self::debug(0, __METHOD__, "");
        $this->assertTrue(true);
    }

    public function testCumulative()
    {
        self::debug(0, __METHOD__, "");
        $result = $this->_testable->depth("TRXBTC");
        $this->assertTrue((isset($result['code']) == false));
        $this->assertTrue(isset($result['bids']));
        $this->assertTrue(isset($result['asks']));
        $result = $this->_testable->cumulative($result);
        $this->assertTrue(isset($result['bids']));
        $this->assertTrue(isset($result['asks']));
        $this->assertTrue(is_array($result['bids']));
        $this->assertTrue(is_array($result['asks']));
        $this->assertTrue(count($result['bids']) > 0);
        $this->assertTrue(count($result['asks']) > 0);
    }

    public function testHighstock()
    {
        self::debug(0, __METHOD__, "");

        $this->_testable->chart([
            "BNBBTC",
        ], "15m", function ($api, $symbol, $chart) {
            echo "{$symbol} chart update\n";
            //print_r($chart);
            $endpoint = strtolower($symbol) . '@kline_' . "15m";
            $api->terminate($endpoint);

            $this->assertTrue($symbol == "BNBBTC");
            $this->assertTrue(is_array($chart));
            $this->assertTrue(count($chart) > 0);

            $result = $this->_testable->highstock($chart, true);
            $this->assertTrue(is_array($result));
            $this->assertTrue(count($result) > 0);
        });
    }

    public function testKline()
    {
        self::debug(0, __METHOD__, "");

        $this->_testable->kline(["BNBBTC"], "5m", function ($api, $symbol, $chart) {
            $endpoint = strtolower($symbol) . '@kline_' . "5m";
            $api->terminate($endpoint);
            $this->assertTrue($symbol == "BNBBTC");
            $this->assertTrue(is_object($chart));
            $this->assertTrue(count(get_object_vars($chart)) > 15);
        });
    }

    public function testDepthHandler()
    {
        self::debug(0, __METHOD__, "");
        $this->assertTrue(true);
    }

    public function testChartHandler()
    {
        self::debug(0, __METHOD__, "");
        $this->assertTrue(true);
    }

    public function testFirst()
    {
        self::debug(0, __METHOD__, "");
        $arr = array(
            "one" => 6,
            "two" => 7,
            "three" => 8,
        );
        $this->assertTrue($this->_testable->first($arr) == "one");
        $arr = array();
        $this->assertTrue($this->_testable->first($arr) == null);
    }

    public function testLast()
    {
        self::debug(0, __METHOD__, "");
        $arr = array(
            "one" => 6,
            "two" => 7,
            "three" => 8,
        );
        $this->assertTrue($this->_testable->last($arr) == "three");
        $arr = array();
        $this->assertTrue($this->_testable->last($arr) == null);
    }

    public function testDisplayDepth()
    {
        self::debug(0, __METHOD__, "");
        $result = $this->_testable->depth("TRXBTC");
        $this->assertTrue((isset($result['code']) == false));
        $this->assertTrue(isset($result['bids']));
        $this->assertTrue(isset($result['asks']));
        $result = $this->_testable->displayDepth($result);
        $this->assertTrue(is_string($result));
        $this->assertTrue($result != "");
    }

    public function testSortDepth()
    {
        self::debug(0, __METHOD__, "");
        $this->assertTrue(true);
    }

    public function testDepthData()
    {
        self::debug(0, __METHOD__, "");
        $this->assertTrue(true);
    }

    public function testDepthCache()
    {
        self::debug(0, __METHOD__, "");

        $count = 0;

        $this->_testable->depthCache([
            "BNBBTC",
        ], function ($api, $symbol, $depth) use (&$count) {
            echo "{$symbol} depth cache update\n";
            $limit = 11; // Show only the closest asks/bids
            $sorted = $api->sortDepth($symbol, $limit);
            //$bid = $api->first($sorted['bids']);
            //$ask = $api->first($sorted['asks']);
            $api->displayDepth($sorted);
            //echo "ask: {$ask}\n";
            //echo "bid: {$bid}\n";

            $this->assertTrue($symbol == "BNBBTC");
            $this->assertTrue(is_array($depth));
            $this->assertTrue(isset($depth['bids']));
            $this->assertTrue(isset($depth['asks']));
            $this->assertTrue(is_array($depth['bids']));
            $this->assertTrue(is_array($depth['asks']));
            $this->assertTrue(count($depth['bids']) > 0);
            $this->assertTrue(count($depth['asks']) > 0);

            $count++;
            if ($count > 2) {
                $endpoint = strtolower($symbol) . '@depthCache';
                $api->terminate($endpoint);
            }
        });
    }

    public function testTrades()
    {
        self::debug(0, __METHOD__, "");

        $count = 0;

        $this->_testable->trades([
            "BNBBTC",
        ], function ($api, $symbol, $trades) use (&$count) {
            echo "{$symbol} trades update" . PHP_EOL;
            //print_r($trades);
            $this->assertTrue($symbol == "BNBBTC");
            $this->assertTrue(is_array($trades));
            $this->assertTrue(count($trades) > 0);
            $count++;
            if ($count > 2) {
                $endpoint = strtolower($symbol) . '@trades';
                $api->terminate($endpoint);
            }
        });
    }

    public function testMiniTicker()
    {
        self::debug(0, __METHOD__, "");

        $count = 0;

        $this->_testable->miniTicker(function ($api, $ticker) use (&$count) {
            //print_r( $ticker );
            $this->assertTrue(is_array($ticker));
            $this->assertTrue(count($ticker) > 0);
            $count++;
            if ($count > 2) {
                $endpoint = '@miniticker';
                $api->terminate($endpoint);
            }
        });
    }

    public function testTicker()
    {
        self::debug(0, __METHOD__, "");

        $count = 0;

        $this->_testable->ticker("BNBBTC", function ($api, $symbol, $ticker) use (&$count) {
            //print_r( $ticker );
            $this->assertTrue(is_array($ticker));
            $this->assertTrue(count($ticker) > 0);
            $count++;
            if ($count > 2) {
                $endpoint = $symbol ? strtolower($symbol) . '@ticker' : '!ticker@arr';
                $api->terminate($endpoint);
            }
        });
    }

    public function testChart()
    {
        self::debug(0, __METHOD__, "");

        $count = 0;

        $this->_testable->chart([
            "BNBBTC",
        ], "15m", function ($api, $symbol, $chart) use (&$count) {
            echo "{$symbol} chart update\n";
            //print_r($chart);

            $this->assertTrue($symbol == "BNBBTC");
            $this->assertTrue(is_array($chart));
            $this->assertTrue(count($chart) > 0);

            $count++;
            if ($count > 2) {
                $endpoint = strtolower($symbol) . '@kline_' . "15m";
                $api->terminate($endpoint);
            }
        });
    }

    public function testUserdata()
    {
        self::debug(0, __METHOD__, "");

        $balance_update = function ($api, $balances) {
            //print_r( $balances );
            self::debug(0, __METHOD__, "Balance update" . PHP_EOL);
            $api->terminate("@userdata");
        };

        $order_update = function ($api, $report) {
            //print_r( $report );
            self::debug(0, __METHOD__, "Order update" . PHP_EOL);
            $api->terminate("@userdata");
        };

        //$this->_testable->userData( $balance_update, $order_update );
    }

    public function testKeepAlive()
    {
        self::debug(0, __METHOD__, "");
        $this->assertTrue(true);
    }

    public function testGetTransfered()
    {
        self::debug(0, __METHOD__, "");
        $result = $this->_testable->getTransfered();
        $this->assertTrue(true);
    }

    public function testGetRequestCount()
    {
        self::debug(0, __METHOD__, "");
        $result = $this->_testable->getRequestCount();
        $this->assertTrue(is_int($result));
        $this->assertTrue($result >= 0);
    }

    public function testAddToTransfered()
    {
        self::debug(0, __METHOD__, "");
        $count = $this->_testable->getRequestCount();
        $trans = $this->_testable->getTransfered();
        $this->_testable->addToTransfered(555);
        $trans1 = $this->_testable->getTransfered();

        $count1 = $this->_testable->getRequestCount();
        $this->assertTrue(is_int($count1));
        $this->assertTrue(($count + 1) == $count1);
        $this->assertTrue(strcmp($trans, $trans1) != 0);
    }

    public function invokeMethod(&$object, $methodName, array $parameters = array())
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

}
