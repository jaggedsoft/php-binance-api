<?php

require '../php-binance-api.php';

$api_key = "z5RQZ9n8JcS3HLDQmPpfLQIGGQN6TTs5pCP5CTnn4nYk2ImFcew49v4ZrmP3MGl5";
$api_secret = "ZqePF1DcLb6Oa0CfcLWH0Tva59y8qBBIqu789JEY27jq0RkOKXpNl9992By1PN9Z";

$api = new Binance\API($api_key, $api_secret);
$result = $api->marketBuy( "BNBBTC", 1 );
print_r( $result ) . "\n";

$api_key = "z5RQZ9n8JcS3HLDQmPpfLQIGGQN6TTs5pCP5CTnn4nYk2ImFcew49v4ZrmP3MGl5";
$api_secret = "intentionally wrong";

$api = new Binance\API($api_key, $api_secret);
$result = $api->marketBuy( "BNBBTC", 1 );
print_r( $result ) . "\n";


$api_key = "YDjDADdXLCkY1BnjFxKVvBzheIyFjtafSU4yyadffiBXdezyViMi0ngiVBawwd3x";
$api_secret = "CtWl7kkYB4eKePyosmuGbJH8FBH4ArTB2qOIedHcOYfzALDG2eD46mWVGsf7lrHJ"; // trading disabled

$api = new Binance\API($api_key, $api_secret);
$result = $api->marketBuy( "BNBBTC", 1 );
print_r( $result ) . "\n";



