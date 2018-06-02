<?php

require 'php-binance-api.php';
require 'vendor/autoload.php';


// @see home_directory_config.php
// use config from ~/.confg/jaggedsoft/php-binance-api.json
$api = new Binance\API();

$count = 0;

$api->miniTicker( function ( $api, $ticker ) use ( &$count ) {
   print_r( $ticker );
   $count++;
   print $count . "\n";
   if($count > 2) {
      $endpoint = '@miniticker';
      $api->terminate( $endpoint );
   }
} );