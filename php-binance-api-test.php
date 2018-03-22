<?php
/* ============================================================
 * php-binance-api
 * https://github.com/jaggedsoft/php-binance-api
 * ============================================================
 * Copyright 2017-, Jon Eyrick
 * Released under the MIT License
 * ============================================================ */

declare(strict_types=1);

require 'php-binance-api.php';
require 'vendor/autoload.php';

file_put_contents( getenv("HOME") . "/.config/jaggedsoft/php-binance-api.json" ,
   "{ \"api-key\": \"z5RQZ9n8JcS3HLDQmPpfLQIGGQN6TTs5pCP5CTnn4nYk2ImFcew49v4ZrmP3MGl5\",
      \"api-secret\": \"ZqePF1DcLb6Oa0CfcLWH0Tva59y8qBBIqu789JEY27jq0RkOKXpNl9992By1PN9Z\" }");

use PHPUnit\Framework\TestCase;

final class BinanceTest extends TestCase
{
   private $_testable = null;

   private static function debug( $pipe, $method, $msg ) {
      $pipe = ( $pipe == 0 ) ? STDOUT : STDERR;
      fwrite( $pipe, $method . ": " . $msg . "\n");
   }
   public static function setUpBeforeClass() {
      self::debug( 0, __METHOD__, "" );
      if( file_exists( getenv("HOME") . "/.config/jaggedsoft/php-binance-api.json" ) == false ) {
         self::debug( 1, __METHOD__, getenv("HOME") . "/.config/jaggedsoft/php-binance-api.json not found" );
         exit;
      }
   }
   protected function setUp() {
      self::debug( 0, __METHOD__, "" );
      $this->_testable = new Binance\API();
      $this->assertInstanceOf('Binance\API', $this->_testable);
   }
   public function testInstantiate() {
      self::debug( 0, __METHOD__, "" );
      $this->_testable = new Binance\API();
      $this->assertInstanceOf('Binance\API', $this->_testable);
   }
   public function testAccount() {
      self::debug( 0, __METHOD__, "" );
      $details = $this->_testable->account();
      $check_keys = array( 'makerCommission',
                           'takerCommission',
                           'buyerCommission',
                           'sellerCommission',
                           'canTrade',
                           'canWithdraw',
                           'canDeposit',
                           'updateTime',
                           'balances' );

      foreach ($check_keys as $check_key) {
         $this->assertTrue( isset( $details[ $check_key ] ) );
         if( isset( $details[ $check_key ] ) == false ) {
            self::debug( 0, __METHOD__, "exchange info error: $check_key missing" );
         }
      }

      $this->assertTrue( count( $details[ 'balances' ] ) > 0 );
   }
   public function testBuy() {
      self::debug( 0, __METHOD__, "" );
      $result = $this->_testable->buy( "TRXBTC", "5", "0.001" );
      $this->assertTrue( isset( $result['code'] ) );

      if( isset( $result['code'] ) ) {
         $this->assertTrue( $result['code'] == "-2010" );
      }

      if( isset( $result['code'] ) == false ) {
         self::debug( 0, __METHOD__, "shouldn't be any money in the account" );
      }
   }
   public function testBuyTest() {
      self::debug( 0, __METHOD__, "" );

      $symbol = "TRXBTC";
      $rangeValues = array(   "99999999.99999999",
                              "1",
                              "1.1",
                              "0.01",
                              "0.001",
                              "0.0001",
                              "0.00001",
                              "0.000001",
                              "0.0000001",
                              "0.00000001" );

      foreach ($$rangeValues as $buyAmount) {
         foreach ($$rangeValues as $buyValue) {
            // string check
            $result = $this->_testable->buyTest( $symbol, $buyAmount, $buyValue );
            $this->assertTrue( ( isset( $result['code'] ) == false ) );

            if( isset( $result['code'] ) ) {
               self::debug( 1, __METHOD__, " buy error: " . $result['code'] . ":" . $result['msg'] );
            }
            // int check
            $result = $this->_testable->buyTest( $symbol, intval( $buyAmount ), intval( $buyValue ) );
            $this->assertTrue( ( isset( $result['code'] ) == false ) );

            if( isset( $result['code'] ) ) {
               self::debug( 1, __METHOD__, " buy error: " . $result['code'] . ":" . $result['msg'] );
            }
            // double check
            $result = $this->_testable->buyTest( $symbol, doubleval( $buyAmount ), doubleval( $buyValue ) );
            $this->assertTrue( ( isset( $result['code'] ) == false ) );

            if( isset( $result['code'] ) ) {
               self::debug( 1, __METHOD__, " buy error: " . $result['code'] . ":" . $result['msg'] );
            }
            // float check
            $result = $this->_testable->buyTest( $symbol, floatval( $buyAmount ), floatval( $buyValue ) );
            $this->assertTrue( ( isset( $result['code'] ) == false ) );

            if( isset( $result['code'] ) ) {
               self::debug( 1, __METHOD__, " buy error: " . $result['code'] . ":" . $result['msg'] );
            }
         }
      }
   }
   public function testSell() {
      self::debug( 0, __METHOD__, "" );
      $result = $this->_testable->sell( "TRXBTC", "5", "0.001" );
      $this->assertTrue( isset( $result['code'] ) );

      if( isset( $result['code'] ) ) {
         $this->assertTrue( $result['code'] == "-2010" );
      }

      if( isset( $result['code'] ) == false ) {
         self::debug( 1, __METHOD__, "shouldn't be any money in the account" );
      }
   }
   public function testSellTest() {
      self::debug( 0, __METHOD__, "" );
      $result = $this->_testable->sellTest( "TRXBTC", "5", "0.001" );
      $this->assertTrue( ( isset( $result['code'] ) == false ) );

      if( isset( $result['code'] ) ) {
         self::debug( 1, __METHOD__, "sell error: " . $result['code'] . ":" . $result['msg'] );
      }
   }
   public function testMarketBuy() {
      self::debug( 0, __METHOD__, "" );

      $symbol = "TRXBTC";
      $rangeValues = array(   "99999999.99999999",
                              "1",
                              "1.1",
                              "0.01",
                              "0.001",
                              "0.0001",
                              "0.00001",
                              "0.000001",
                              "0.0000001",
                              "0.00000001" );

      foreach ($$rangeValues as $buyValue) {
         // string check
         $result = $this->_testable->marketBuy( $symbol, $buyAmount, $buyValue );
         $this->assertTrue( ( isset( $result['code'] ) == false ) );

         if( isset( $result['code'] ) ) {
            $this->assertTrue( $result['code'] == "-2010" );
         }

         if( isset( $result['code'] ) == false ) {
            self::debug( 1, __METHOD__, " buy error: " . $result['code'] . ":" . $result['msg'] );
         }
         // int check
         $result = $this->_testable->marketBuy( $symbol, intval( $buyAmount ) );
         $this->assertTrue( ( isset( $result['code'] ) == false ) );

         if( isset( $result['code'] ) ) {
            $this->assertTrue( $result['code'] == "-2010" );
         }

         if( isset( $result['code'] ) == false ) {
            self::debug( 1, __METHOD__, " buy error: " . $result['code'] . ":" . $result['msg'] );
         }
         // double check
         $result = $this->_testable->marketBuy( $symbol, doubleval( $buyAmount ) );
         $this->assertTrue( ( isset( $result['code'] ) == false ) );

         if( isset( $result['code'] ) ) {
            $this->assertTrue( $result['code'] == "-2010" );
         }

         if( isset( $result['code'] ) == false ) {
            self::debug( 1, __METHOD__, " buy error: " . $result['code'] . ":" . $result['msg'] );
         }
         // float check
         $result = $this->_testable->marketBuy( $symbol, floatval( $buyAmount ) );
         $this->assertTrue( ( isset( $result['code'] ) == false ) );

         if( isset( $result['code'] ) ) {
            $this->assertTrue( $result['code'] == "-2010" );
         }

         if( isset( $result['code'] ) == false ) {
            self::debug( 1, __METHOD__, " buy error: " . $result['code'] . ":" . $result['msg'] );
         }
      }
   }
   public function testMarketBuyTest() {
      self::debug( 0, __METHOD__, "" );
      $result = $this->_testable->marketBuyTest( "TRXBTC", "5" );
      $this->assertTrue( ( isset( $result['code'] ) == false ) );

      if( isset( $result['code'] ) ) {
         self::debug( 1, __METHOD__, "market buy error: " . $result['code'] . ":" . $result['msg']);
      }
   }
   public function testMarketSell() {
      self::debug( 0, __METHOD__, "" );
      $result = $this->_testable->marketSell( "TRXBTC", "5" );
      $this->assertTrue( isset( $result['code'] ) );

      if( isset( $result['code'] ) ) {
         $this->assertTrue( $result['code'] == "-2010" );
      }

      if( isset( $result['code'] ) == false ) {
         self::debug( 1, __METHOD__, "shouldn't be any code in the account");
      }
   }
   public function testMarketSellTest() {
      self::debug( 0, __METHOD__, "" );
      $result = $this->_testable->marketSellTest( "TRXBTC", "5" );
      $this->assertTrue( ( isset( $result['code'] ) == false ) );

      if( isset( $result['code'] ) ) {
         self::debug( 1, __METHOD__, "market sell error: " . $result['code'] . ":" . $result['msg']);
      }
   }
   public function testCancel() {
      self::debug( 0, __METHOD__, "" );
      $result = $this->_testable->cancel( "TRXBTC", "55555555" );
      $this->assertTrue( isset( $result['code'] ) );

      if( isset( $result['code'] ) ) {
         $this->assertTrue( $result['code'] == "-2011" );
      }

      if( isset( $result['code'] ) == false ) {
         self::debug( 1, __METHOD__, "shouldn't be anything to cancel");
      }
   }
   public function testOrderStatus() {
      self::debug( 0, __METHOD__, "" );
      $result = $this->_testable->orderStatus( "TRXBTC", "55555555" );
      $this->assertTrue( isset( $result['code'] ) );

      if( isset( $result['code'] ) ) {
         $this->assertTrue( $result['code'] == "-2013" );
      }

      if( isset( $result['code'] ) == false ) {
         self::debug( 1, __METHOD__, "shouldn't be any order with this id");
      }
   }
   public function testOpenOrders() {
      self::debug( 0, __METHOD__, "" );
      $result = $this->_testable->openOrders( "TRXBTC" );
      $this->assertTrue( ( isset( $result['code'] ) == false ) );
      $this->assertTrue( is_array( $result ) );

      if( isset( $result['code'] ) ) {
         self::debug( 1, __METHOD__, "open orders error: " . $result['code'] . ":" . $result['msg']);
      }
   }
   public function testOrders() {
      self::debug( 0, __METHOD__, "" );
      $result = $this->_testable->orders( "TRXBTC" );
      $this->assertTrue( ( isset( $result['code'] ) == false ) );
      $this->assertTrue( is_array( $result ) );

      if( isset( $result['code'] ) ) {
         self::debug( 1, __METHOD__, "orders error: " . $result['code'] . ":" . $result['msg']);
      }
   }
   public function testHistory() {
      self::debug( 0, __METHOD__, "" );
      $result = $this->_testable->history( "TRXBTC" );
      $this->assertTrue( ( isset( $result['code'] ) == false ) );
      $this->assertTrue( is_array( $result ) );

      if( isset( $result['code'] ) ) {
         self::debug( 1, __METHOD__, "my trades error: " . $result['code'] . ":" . $result['msg']);
      }
   }
   public function testUseServerTime() {
      self::debug( 0, __METHOD__, "" );
      $this->_testable->useServerTime();
      $this->assertTrue( true );
   }
   public function testTime() {
      self::debug( 0, __METHOD__, "" );
      $result = $this->_testable->time();
      $this->assertTrue( ( isset( $result['code'] ) == false ) );
      $this->assertTrue( isset( $result[ 'serverTime' ] ) );

      if( isset( $result['code'] ) ) {
         self::debug( 1, __METHOD__, "server time error: " . $result['code'] . ":" . $result['msg']);
      }
   }
   public function testExchangeInfo() {
      self::debug( 0, __METHOD__, "" );
      $result = $this->_testable->exchangeInfo();
      $this->assertTrue( ( isset( $result['code'] ) == false ) );
      $check_keys = array( 'timezone', 'serverTime', 'rateLimits', 'exchangeFilters', 'symbols' );

      foreach ($check_keys as $check_key) {
         $this->assertTrue( isset( $result[ $check_key ] ) );
         if( isset( $result[ $check_key ] ) == false ) {
            self::debug( 1, __METHOD__, "exchange info error: $check_key missing\n");
         }
      }

      $this->assertTrue( count( $result[ 'symbols' ] ) > 0 );
      $this->assertTrue( count( $result[ 'rateLimits' ] ) > 0 );

      if( isset( $result['code'] ) ) {
         self::debug( 1, __METHOD__, "exchange info error: " . $result['code'] . ":" . $result['msg']);
      }
   }
   public function testWithdraw() {
      self::debug( 0, __METHOD__, "" );
      $asset = "BTC";
      $address = "1C5gqLRs96Xq4V2ZZAR1347yUCpHie7sa";
      $amount = 0.2;
      $result = $this->_testable->withdraw($asset, $address, $amount, false);
      $this->assertTrue( true );
      if( isset( $result['code'] ) ) {
         self::debug( 1, __METHOD__, "withdraw error: " . $result['code'] . ":" . $result['msg']);
      }
   }
   public function testDepositAddress() {
      self::debug( 0, __METHOD__, "" );
      $asset = "BTC";
      $result = $this->_testable->depositAddress($asset);
      $this->assertTrue( true );
      if( isset( $result['code'] ) ) {
         self::debug( 1, __METHOD__, "depsoit error: " . $result['code'] . ":" . $result['msg']);
      }
   }
   public function testDepositHistory() {
      self::debug( 0, __METHOD__, "" );
      $asset = "BTC";
      $result = $this->_testable->depositHistory();
      $this->assertTrue( true );
      if( isset( $result['code'] ) ) {
         self::debug( 1, __METHOD__, "deposit history error: " . $result['code'] . ":" . $result['msg']);
      }
   }
   public function testWithdrawHistory() {
      self::debug( 0, __METHOD__, "" );
      $asset = "BTC";
      $result = $this->_testable->withdrawHistory();
      $this->assertTrue( true );
      if( isset( $result['code'] ) ) {
         self::debug( 1, __METHOD__, "withdraw history error: " . $result['code'] . ":" . $result['msg']);
      }
   }
   public function testPrices() {
      self::debug( 0, __METHOD__, "" );
      $result = $this->_testable->prices();
      $this->assertTrue( ( isset( $result['code'] ) == false ) );
      $this->assertTrue( count( $result ) > 0 );

      if( isset( $result['code'] ) ) {
         self::debug( 1, __METHOD__, "prices error: " . $result['code'] . ":" . $result['msg']);
      }
   }
   public function testBookPrices() {
      self::debug( 0, __METHOD__, "" );
      $result = $this->_testable->bookPrices();
      $this->assertTrue( ( isset( $result['code'] ) == false ) );
      $this->assertTrue( count( $result ) > 0 );

      if( isset( $result['code'] ) ) {
         self::debug( 1, __METHOD__, "book prices error: " . $result['code'] . ":" . $result['msg']);
      }
   }
   public function testPrevDay() {
      self::debug( 0, __METHOD__, "" );
      $result = $this->_testable->prevDay( "TRXBTC" );
      $this->assertTrue( ( isset( $result['code'] ) == false ) );

      if( isset( $result['code'] ) ) {
         self::debug( 1, __METHOD__, "previous day error: " . $result['code'] . ":" . $result['msg']);
      }
   }
   public function testAggTrades() {
      self::debug( 0, __METHOD__, "" );
      $result = $this->_testable->aggTrades( "TRXBTC" );
      $this->assertTrue( ( isset( $result['code'] ) == false ) );
      $this->assertTrue( count( $result ) > 0 );

      if( isset( $result['code'] ) ) {
         self::debug( 1, __METHOD__, "agg trades error: " . $result['code'] . ":" . $result['msg']);
      }
   }
   public function testDepth() {
      self::debug( 0, __METHOD__, "" );
      $result = $this->_testable->depth( "TRXBTC" );
      $this->assertTrue( ( isset( $result['code'] ) == false ) );
      $this->assertTrue( isset( $result['bids'] ) );
      $this->assertTrue( isset( $result['asks'] ) );

      if( isset( $result['code'] ) ) {
         self::debug( 1, __METHOD__, "depth error: " . $result['code'] . ":" . $result['msg']);
      }
   }
   public function testBalances() {
      self::debug( 0, __METHOD__, "" );
      $result = $this->_testable->balances();
      $this->assertTrue( ( isset( $result['code'] ) == false ) );
      $this->assertTrue( is_array( $result ) );

      if( isset( $result['code'] ) ) {
         self::debug( 1, __METHOD__, "balances error: " . $result['code'] . ":" . $result['msg']);
      }
   }
   public function testGetProxyUriString() {
      self::debug( 0, __METHOD__, "" );

      $proxyConf = [
        'proto' => 'http',
        'address' => '192.168.1.1',
        'port' => '8080',
        'user' => 'dude',
        'pass' => 'd00d'
      ];

      $this->_testable->setProxy( $proxyConf );
      $uri = $this->_testable->getProxyUriString();
      $this->assertTrue( $uri == $proxyConf['proto'] . "://" . $proxyConf['address'] . ":" . $proxyConf['port'] );
   }
   public function testHttpRequest() {
      self::debug( 0, __METHOD__, "" );
      $this->assertTrue( true );
   }
   public function testOrder() {
      self::debug( 0, __METHOD__, "" );
      $this->assertTrue( true );
   }
   public function testCandlesticks() {
      self::debug( 0, __METHOD__, "" );
      $result = $this->_testable->candlesticks("BNBBTC", "5m");
      $this->assertTrue( is_array( $result ) );

      if( isset( $result['code'] ) ) {
         self::debug( 1, __METHOD__, "candlesticks error: " . $result['code'] . ":" . $result['msg']);
      }
   }
   public function testBalanceData() {
      self::debug( 0, __METHOD__, "" );
      $ticker = $this->_testable->prices();
      $result = $this->_testable->balances( $ticker );
      $this->assertTrue( is_array( $result ) );

      if( isset( $result['code'] ) ) {
         self::debug( 1, __METHOD__, "balances error: " . $result['code'] . ":" . $result['msg']);
      }
   }
   public function testBalanceHandler() {
      self::debug( 0, __METHOD__, "" );
      $this->assertTrue( true );
   }
   public function testTickerStreamHandler() {
      self::debug( 0, __METHOD__, "" );
      $this->assertTrue( true );
   }
   public function testExecutionHandler() {
      self::debug( 0, __METHOD__, "" );
      $this->assertTrue( true );
   }
   public function testChartData() {
      self::debug( 0, __METHOD__, "" );
      $this->assertTrue( true );
   }
   public function testBookPriceData() {
      self::debug( 0, __METHOD__, "" );
      $this->assertTrue( true );
   }
   public function testPriceData() {
      self::debug( 0, __METHOD__, "" );
      $this->assertTrue( true );
   }
   public function testCumulative() {
      self::debug( 0, __METHOD__, "" );
      $result = $this->_testable->depth( "TRXBTC" );
      $this->assertTrue( ( isset( $result['code'] ) == false ) );
      $this->assertTrue( isset( $result['bids'] ) );
      $this->assertTrue( isset( $result['asks'] ) );
      $result = $this->_testable->cumulative( $result );
      $this->assertTrue( isset( $result['bids'] ) );
      $this->assertTrue( isset( $result['asks'] ) );
      $this->assertTrue( is_array( $result['bids'] ) );
      $this->assertTrue( is_array( $result['asks'] ) );
      $this->assertTrue( count( $result['bids'] ) > 0);
      $this->assertTrue( count( $result['asks'] ) > 0);
   }
   public function testHighstock() {
      self::debug( 0, __METHOD__, "" );
      $this->assertTrue( true );
   }
   public function testDepthHandler() {
      self::debug( 0, __METHOD__, "" );
      $this->assertTrue( true );
   }
   public function testChartHandler() {
      self::debug( 0, __METHOD__, "" );
      $this->assertTrue( true );
   }
   public function testFirst() {
      self::debug( 0, __METHOD__, "" );
      $arr = array( "one" => 6, "two" => 7, "three" => 8 );
      $this->assertTrue( $this->_testable->first( $arr ) == "one" );
      $arr = array();
      $this->assertTrue( $this->_testable->first( $arr ) == null );
   }
   public function testLast() {
      self::debug( 0, __METHOD__, "" );
      $arr = array( "one" => 6, "two" => 7, "three" => 8 );
      $this->assertTrue( $this->_testable->last( $arr ) == "three" );
      $arr = array();
      $this->assertTrue( $this->_testable->last( $arr ) == null );
   }
   public function testDisplayDepth() {
      self::debug( 0, __METHOD__, "" );
      $result = $this->_testable->depth( "TRXBTC" );
      $this->assertTrue( ( isset( $result['code'] ) == false ) );
      $this->assertTrue( isset( $result['bids'] ) );
      $this->assertTrue( isset( $result['asks'] ) );
      $result = $this->_testable->displayDepth( $result );
      $this->assertTrue( is_string( $result ) );
      $this->assertTrue( $result != "" );
   }
   public function testSortDepth() {
      self::debug( 0, __METHOD__, "" );
      $this->assertTrue( true );
   }
   public function testDepthData() {
      self::debug( 0, __METHOD__, "" );
      $this->assertTrue( true );
   }
   public function testDepthCache() {
      self::debug( 0, __METHOD__, "" );
      $this->assertTrue( true );
   }

   public function testTicker() {
      self::debug( 0, __METHOD__, "" );
      $this->assertTrue( true );
   }

   public function testChart() {
      self::debug( 0, __METHOD__, "" );
      $this->assertTrue( true );
   }

   public function testKeepAlive() {
      self::debug( 0, __METHOD__, "" );
      $this->assertTrue( true );
   }

   public function testMiniTicker() {
      self::debug( 0, __METHOD__, "" );
      $this->assertTrue( true );
   }

   public function testGetTransfered() {
      self::debug( 0, __METHOD__, "" );
      $result = $this->_testable->getTransfered();
      $this->assertTrue( true );
   }

   public function testGetRequestCount() {
      self::debug( 0, __METHOD__, "" );
      $result = $this->_testable->getRequestCount();
      $this->assertTrue( true );
   }
}
