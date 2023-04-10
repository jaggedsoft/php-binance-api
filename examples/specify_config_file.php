<?php

require '../php-binance-api.php';

// @see home_directory_config.php
// use config from ~/.confg/jaggedsoft/php-binance-api.json
$api = new Binance\API( getenv( "HOME" ) . "/.config/jaggedsoft/php-binance-api.json" );

$account = $api->account();
print_r($account); 
