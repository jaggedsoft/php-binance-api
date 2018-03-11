<?php

require '../php-binance-api.php';

$api = new Binance\API();

$ticker = $api->prices();
print_r($ticker); // List prices of all symbols
echo "Price of BNB: {$ticker['BNBBTC']} BTC.".PHP_EOL;
