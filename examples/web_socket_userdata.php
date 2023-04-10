<?php

require 'php-binance-api.php';
require 'vendor/autoload.php';


// @see home_directory_config.php
// use config from ~/.confg/jaggedsoft/php-binance-api.json
$api = new Binance\API();

$balance_update = function($api, $balances) {
   print_r($balances);
   echo "Balance update".PHP_EOL;
};

$order_update = function($api, $report) {
   echo "Order update".PHP_EOL;
   print_r($report);
   $price = $report['price'];
   $quantity = $report['quantity'];
   $symbol = $report['symbol'];
   $side = $report['side'];
   $orderType = $report['orderType'];
   $orderId = $report['orderId'];
   $orderStatus = $report['orderStatus'];
   $executionType = $report['orderStatus'];
   if ( $executionType == "NEW" ) {
      if ( $executionType == "REJECTED" ) {
         echo "Order Failed! Reason: {$report['rejectReason']}".PHP_EOL;
      }
      echo "{$symbol} {$side} {$orderType} ORDER #{$orderId} ({$orderStatus})".PHP_EOL;
      echo "..price: {$price}, quantity: {$quantity}".PHP_EOL;
      return;
   }
   //NEW, CANCELED, REPLACED, REJECTED, TRADE, EXPIRED
   echo "{$symbol} {$side} {$executionType} {$orderType} ORDER #{$orderId}".PHP_EOL;
};

$api->userData($balance_update, $order_update);