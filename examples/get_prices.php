<?php

require '../php-binance-api.php';

// @see home_directory_config.php
// use config from ~/.confg/jaggedsoft/php-binance-api.json
$api = new Binance\API();

// Get latest price of all symbols
$tickers = $api->prices();
print_r($tickers); // List prices of all symbols

// Get latest price of a symbol
$price = $api->price('BNBBTC');
echo "Price of BNB: {$price} BTC.\n";
