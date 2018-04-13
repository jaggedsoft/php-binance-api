<?php

require '../php-binance-api.php';

// @see home_directory_config.php
// use config from ~/.confg/jaggedsoft/php-binance-api.json
$api = new Binance\API( "/home/dave/.config/jaggedsoft/php-binance-api-1.json" );

// Get all of your positions, including estimated BTC value
$balances = $api->balances();
print_r($balances);
