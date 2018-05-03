<?php

require '../php-binance-api.php';

$api_key = "z5RQZ9n8JcS3HLDQmPpfLQIGGQN6TTs5pCP5CTnn4nYk2ImFcew49v4ZrmP3MGl5";
$api_secret = "ZqePF1DcLb6Oa0CfcLWH0Tva59y8qBBIqu789JEY27jq0RkOKXpNl9992By1PN9Z";

$api1 = new Binance\API();
$balances1 = $api1->balances();
print count($balances1) . "\n";

$api2 = new Binance\API( "/home/dave/.config/jaggedsoft/php-binance-api.json" );
$balances2 = $api2->balances();
print count($balances2) . "\n";

$api3 = new Binance\API($api_key, $api_secret);
$balances3 = $api3->balances();
print count($balances3) . "\n";



