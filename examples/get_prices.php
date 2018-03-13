<?php

require '../php-binance-api.php';

// @see home_directory_config.php
// use config from ~/.confg/jaggedsoft/php-binance-api.json
$api = new Binance\API();

// Get latest price of a symbol
$ticker = $api->prices();
print_r($ticker); // List prices of all symbols
echo "Price of BNB: {$ticker['BNBBTC']} BTC.\n";
