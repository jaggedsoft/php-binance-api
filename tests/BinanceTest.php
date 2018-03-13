<?php
/* ============================================================
 * php-binance-api
 * https://github.com/jaggedsoft/php-binance-api
 * ============================================================
 * Copyright 2017-, Jon Eyrick
 * Released under the MIT License
 * ============================================================ */

declare(strict_types=1);

require '../php-binance-api.php';

use PHPUnit\Framework\TestCase;

final class BinanceTest extends TestCase
{
   private $_testable = null;

   public static function setUpBeforeClass() {
      fwrite(STDOUT, __METHOD__ . "\n");
      if( file_exists( getenv("HOME") . "/.config/jaggedsoft/php-binance-api.json" ) == false ) {
         fwrite(STDERR, __METHOD__ . ": " . getenv("HOME") . "/.config/jaggedsoft/php-binance-api.json not found\n");
         exit;
      }
   }

   protected function setUp() {
      fwrite(STDOUT, __METHOD__ . "\n");
      $this->_testable = new Binance\API();
      $this->assertInstanceOf('Binance\API', $this->_testable);
   }

   public function testInstantiate() {
      fwrite(STDOUT, __METHOD__ . "\n");
      $this->assertTrue( is_array( $this->_testable->exchangeInfo() ) );
   }
   public function testAccountInfo() {
      fwrite(STDOUT, __METHOD__ . "\n");
      $this->assertTrue( is_array( $this->_testable->account() ) );
   }
   public function testBuy() {
      // todo
      fwrite(STDOUT, __METHOD__ . "\n");
      $this->assertTrue( true );
   }
   public function testBuyTest() {
      // todo
      fwrite(STDOUT, __METHOD__ . "\n");
      $this->assertTrue( true );
   }
   public function testSell() {
      // todo
      fwrite(STDOUT, __METHOD__ . "\n");
      $this->assertTrue( true );
   }
   public function testSellTest() {
      // todo
      fwrite(STDOUT, __METHOD__ . "\n");
      $this->assertTrue( true );
   }
   public function testMarketBuy() {
      // todo
      fwrite(STDOUT, __METHOD__ . "\n");
      $this->assertTrue( true );
   }
   public function testMarketBuyTest() {
      // todo
      fwrite(STDOUT, __METHOD__ . "\n");
      $this->assertTrue( true );
   }
   public function testMarketSell() {
      // todo
      fwrite(STDOUT, __METHOD__ . "\n");
      $this->assertTrue( true );
   }
   public function testMarketSellTest() {
      // todo
      fwrite(STDOUT, __METHOD__ . "\n");
      $this->assertTrue( true );
   }
   public function testCancel() {
      // todo
      fwrite(STDOUT, __METHOD__ . "\n");
      $this->assertTrue( true );
   }
   public function testOrderStatus() {
      // todo
      fwrite(STDOUT, __METHOD__ . "\n");
      $this->assertTrue( true );
   }
   public function testOpenOrders() {
      // todo
      fwrite(STDOUT, __METHOD__ . "\n");
      $this->assertTrue( true );
   }
   public function testOrders() {
      // todo
      fwrite(STDOUT, __METHOD__ . "\n");
      $this->assertTrue( true );
   }
   public function testHstory() {
      // todo
      fwrite(STDOUT, __METHOD__ . "\n");
      $this->assertTrue( true );
   }
   public function testUseServerTime() {
      // todo
      fwrite(STDOUT, __METHOD__ . "\n");
      $this->assertTrue( true );
   }
   public function testTime() {
      // todo
      fwrite(STDOUT, __METHOD__ . "\n");
      $this->assertTrue( true );
   }
   public function testExchangeInfo() {
      fwrite(STDOUT, __METHOD__ . "\n");
      $this->assertTrue( is_array( $this->_testable->exchangeInfo() ) );
   }
   public function testWithdraw() {
      // todo
      fwrite(STDOUT, __METHOD__ . "\n");
      $this->assertTrue( true );
   }
   public function testDepositAddress() {
      // todo
      fwrite(STDOUT, __METHOD__ . "\n");
      $this->assertTrue( true );
   }
   public function testDepositHistory() {
      // todo
      fwrite(STDOUT, __METHOD__ . "\n");
      $this->assertTrue( true );
   }
   public function testWithdrawHistory() {
      // todo
      fwrite(STDOUT, __METHOD__ . "\n");
      $this->assertTrue( true );
   }
   public function testPrices() {
      // todo
      fwrite(STDOUT, __METHOD__ . "\n");
      $this->assertTrue( true );
   }
   public function testBookPrices() {
      // todo
      fwrite(STDOUT, __METHOD__ . "\n");
      $this->assertTrue( true );
   }
   public function testAccount() {
      // todo
      fwrite(STDOUT, __METHOD__ . "\n");
      $this->assertTrue( true );
   }
   public function testPrevDay() {
      // todo
      fwrite(STDOUT, __METHOD__ . "\n");
      $this->assertTrue( true );
   }
   public function testAggTrades() {
      // todo
      fwrite(STDOUT, __METHOD__ . "\n");
      $this->assertTrue( true );
   }
   public function testDepth() {
      // todo
      fwrite(STDOUT, __METHOD__ . "\n");
      $this->assertTrue( true );
   }
   public function testBalances() {
      // todo
      fwrite(STDOUT, __METHOD__ . "\n");
      $this->assertTrue( true );
   }
   public function testGetProxyUriString() {
      // todo
      fwrite(STDOUT, __METHOD__ . "\n");
      $this->assertTrue( true );
   }
   public function testHttpRequest() {
      // todo
      fwrite(STDOUT, __METHOD__ . "\n");
      $this->assertTrue( true );
   }
   public function testOrder() {
      // todo
      fwrite(STDOUT, __METHOD__ . "\n");
      $this->assertTrue( true );
   }
   public function testOrderTest() {
      // todo
      fwrite(STDOUT, __METHOD__ . "\n");
      $this->assertTrue( true );
   }
   public function testCandlesticks() {
      // todo
      fwrite(STDOUT, __METHOD__ . "\n");
      $this->assertTrue( true );
   }
   public function testBalanceData() {
      // todo
      fwrite(STDOUT, __METHOD__ . "\n");
      $this->assertTrue( true );
   }
   public function testBalanceHandler() {
      // todo
      fwrite(STDOUT, __METHOD__ . "\n");
      $this->assertTrue( true );
   }
   public function testTickerStreamHandler() {
      // todo
      fwrite(STDOUT, __METHOD__ . "\n");
      $this->assertTrue( true );
   }
   public function testExecutionHandler() {
      // todo
      fwrite(STDOUT, __METHOD__ . "\n");
      $this->assertTrue( true );
   }
   public function testChartData() {
      // todo
      fwrite(STDOUT, __METHOD__ . "\n");
      $this->assertTrue( true );
   }
   public function testTradesData() {
      // todo
      fwrite(STDOUT, __METHOD__ . "\n");
      $this->assertTrue( true );
   }
   public function testBookPriceData() {
      // todo
      fwrite(STDOUT, __METHOD__ . "\n");
      $this->assertTrue( true );
   }
   public function testPriceData() {
      // todo
      fwrite(STDOUT, __METHOD__ . "\n");
      $this->assertTrue( true );
   }
   public function testCumulative() {
      // todo
      fwrite(STDOUT, __METHOD__ . "\n");
      $this->assertTrue( true );
   }
   public function testHighstock() {
      // todo
      fwrite(STDOUT, __METHOD__ . "\n");
      $this->assertTrue( true );
   }
   public function testDepthHandler() {
      // todo
      fwrite(STDOUT, __METHOD__ . "\n");
      $this->assertTrue( true );
   }
   public function testChartHandler() {
      // todo
      fwrite(STDOUT, __METHOD__ . "\n");
      $this->assertTrue( true );
   }
   public function testFirst() {
      // todo
      fwrite(STDOUT, __METHOD__ . "\n");
      $this->assertTrue( true );
   }
   public function testLast() {
      // todo
      fwrite(STDOUT, __METHOD__ . "\n");
      $this->assertTrue( true );
   }
   public function testDisplayDepth() {
      // todo
      fwrite(STDOUT, __METHOD__ . "\n");
      $this->assertTrue( true );
   }
   public function testSortDepth() {
      // todo
      fwrite(STDOUT, __METHOD__ . "\n");
      $this->assertTrue( true );
   }
   public function testDepthData() {
      // todo
      fwrite(STDOUT, __METHOD__ . "\n");
      $this->assertTrue( true );
   }
   public function testDepthCache() {
      // todo
      fwrite(STDOUT, __METHOD__ . "\n");
      $this->assertTrue( true );
   }

   public function testTicker() {
      // todo
      fwrite(STDOUT, __METHOD__ . "\n");
      $this->assertTrue( true );
   }

   public function testChart() {
      // todo
      fwrite(STDOUT, __METHOD__ . "\n");
      $this->assertTrue( true );
   }

   public function testKeepAlive() {
      // todo
      fwrite(STDOUT, __METHOD__ . "\n");
      $this->assertTrue( true );
   }

   public function testMiniTicker() {
      // todo
      fwrite(STDOUT, __METHOD__ . "\n");
      $this->assertTrue( true );
   }

   public function testGetTransfered() {
      // todo
      fwrite(STDOUT, __METHOD__ . "\n");
      $this->assertTrue( true );
   }

   public function testGetRequestCount() {
      // todo
      fwrite(STDOUT, __METHOD__ . "\n");
      $this->assertTrue( true );
   }
}
