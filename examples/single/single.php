<?php
// Works without composer, supports php 5.6
require 'binance-api-single.php';
$api = new Binance("<key>","<secret>");

// Get latest price of all symbols
$tickers = $api->prices();
print_r($tickers); // List prices of all symbols

// Get latest price of a symbol
$price = $api->price("BNBBTC");
echo "Price of BNB: {$price} BTC.".PHP_EOL;

// Get balances for all of your positions, including estimated BTC value
$balances = $api->balances($tickers);
print_r($balances);
echo "BTC owned: ".$balances['BTC']['available'].PHP_EOL;
echo "ETH owned: ".$balances['ETH']['available'].PHP_EOL;
echo "Estimated Value: ".$api->btc_value." BTC".PHP_EOL;

// Getting 24hr ticker price change statistics for a symbol
$prevDay = $api->prevDay("BNBBTC");
print_r($prevDay);
echo "BNB price change since yesterday: ".$prevDay['priceChangePercent']."%".PHP_EOL;
