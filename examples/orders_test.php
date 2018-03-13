<?php

require '../php-binance-api.php';

// @see home_directory_config.php
// use config from ~/.confg/jaggedsoft/php-binance-api.json
$api = new Binance\API();

// Place a LIMIT order
$quantity = 1000;
$price = 0.0005;
$order = $api->buyTest("BNBBTC", $quantity, $price);
print_r($order);

// Place a MARKET order
$quantity = 1;
$order = $api->buyTest("BNBBTC", $quantity, 0, "MARKET");
print_r($order);

// Place a STOP LOSS order
// When the stop is reached, a stop order becomes a market order
$quantity = 1;
$price = 0.5; // Try to sell it for 0.5 btc
$stopPrice = 0.4; // Sell immediately if price goes below 0.4 btc
$order = $api->sellTest("BNBBTC", $quantity, $price, "STOP_LOSS", ["stopPrice"=>$stopPrice]);
print_r($order);

// Place a take profit order
// When the stop is reached, a stop order becomes a market order
$quantity = 1;
$price = 0.5; // Try to sell it for 0.5 btc
$stopPrice = 0.4; // Sell immediately if price goes below 0.4 btc
$order = $api->sellTest("TRXBTC", $quantity, $price, "TAKE_PROFIT", ["stopPrice"=>$stopPrice]);
print_r($order);

// Place an ICEBERG order
// Iceberg orders are intended to conceal the true order quantity.
$quantity = 20;
$price = 0.5;
$icebergQty = 10;
$order = $api->sellTest("BNBBTC", $quantity, $price, "LIMIT", ["icebergQty"=>$icebergQty]);
print_r($order);
