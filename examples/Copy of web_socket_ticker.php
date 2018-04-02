<?php

require 'php-binance-api.php';
require 'vendor/autoload.php';


// @see home_directory_config.php
// use config from ~/.confg/jaggedsoft/php-binance-api.json
$api = new Binance\API();

$api->miniTicker(function($api, $ticker) {
 print_r($ticker);
 $endpoint = '@miniticker';
 $api->terminate( $endpoint );
});