<?php

require 'php-binance-api.php';
require 'vendor/autoload.php';

// @see home_directory_config.php
// use config from ~/.confg/jaggedsoft/php-binance-api.json
$api = new Binance\API();

$api->depthCache(["BNBBTC"], function($api, $symbol, $depth) {
    echo "{$symbol} depth cache update\n";
    $limit = 11; // Show only the closest asks/bids
    $sorted = $api->sortDepth($symbol, $limit);
    $bid = $api->first($sorted['bids']);
    $ask = $api->first($sorted['asks']);
    echo $api->displayDepth($sorted);
    echo "ask: {$ask}\n";
    echo "bid: {$bid}\n";
    $endpoint = strtolower( $symbol ) . '@depthCache';
    $api->terminate( $endpoint );
});