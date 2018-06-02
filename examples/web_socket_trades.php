<?php

require 'php-binance-api.php';
require 'vendor/autoload.php';


// @see home_directory_config.php
// use config from ~/.confg/jaggedsoft/php-binance-api.json
$api = new Binance\API();

// Trade Updates via WebSocket
$api->trades(["BNBBTC"], function($api, $symbol, $trades) {
    echo "{$symbol} trades update".PHP_EOL;
    print_r($trades);
    $endpoint = strtolower( $symbol ) . '@trades';
    $api->terminate( $endpoint );
});
