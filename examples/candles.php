<?php

require '../php-binance-api.php';

// @see home_directory_config.php
// use config from ~/.confg/jaggedsoft/php-binance-api.json
$api = new Binance\API();

// Get Kline/candlestick data for a symbol
// Periods: 1m,3m,5m,15m,30m,1h,2h,4h,6h,8h,12h,1d,3d,1w,1M
$ticks = $api->candlesticks("EOSBTC", "1m");
print_r($ticks);
//$ticks = $api->candlesticks("BNBBTC", "15m");
//print_r($ticks);
//$ticks = $api->candlesticks("BNBBTC", "8h");
//print_r($ticks);
//$ticks = $api->candlesticks("BNBBTC", "2h");
//print_r($ticks);
//$ticks = $api->candlesticks("BNBBTC", "1d");
//print_r($ticks);
//$ticks = $api->candlesticks("BNBBTC", "30m");
//print_r($ticks);
//$ticks = $api->candlesticks("BNBBTC", "1M");
//print_r($ticks);
