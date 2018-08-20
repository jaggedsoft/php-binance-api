<?php

require '../php-binance-api.php';
require '../php-binance-api-rate-limiter.php';

// use config from ~/.confg/jaggedsoft/php-binance-api.json
$api = new Binance\API();
$api = new Binance\RateLimiter($api);

// Get latest price of a symbol
$price = $api->price("BNBBTC");
echo "Price of BNB: {$price} BTC.\n";

while(true)
{
    $api->openOrders("BNBBTC");
}