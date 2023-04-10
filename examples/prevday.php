<?php

require '../php-binance-api.php';

// use config from ~/.confg/jaggedsoft/php-binance-api.json
$api = new Binance\API();

// Getting 24hr ticker price change statistics for a symbol
$prevDay = $api->prevDay("BNBBTC");
print_r($prevDay);
echo "BNB price change since yesterday: ".$prevDay['priceChangePercent']."%".PHP_EOL;

// Getting 24hr ticker price change statistics for all symbols
$prevDay = $api->prevDay();
print_r($prevDay);