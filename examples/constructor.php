<?php

require '../php-binance-api.php';

$api_key = "gW3TsYrpPoSBEX61rvGfor00ATivkbXNp3ODnNhmQpQjv4nfl8LnGXOX1iL9LcHa";
$api_secret = "bqNINND57jzM8wDYVi6AWRjE9uzF4Q6BJ0V0FvJzdLUmq7c0uhvwUugeW67hx8Bm";

$api1 = new Binance\API();
$balances1 = $api1->balances();
print count($balances1) . "\n";

$api2 = new Binance\API( "/home/dave/.config/jaggedsoft/php-binance-api.json" );
$balances2 = $api2->balances();
print count($balances2) . "\n";

$api3 = new Binance\API($api_key, $api_secret);
$balances3 = $api3->balances();
print count($balances3) . "\n";



