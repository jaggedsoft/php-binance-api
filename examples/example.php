<?php
require 'php-binance-api.php';
$api = new Binance("<api key>","<secret>");

// Get latest price of a symbol
$ticker = $api->prices();
print_r($ticker); // List prices of all symbols
echo "Price of BNB: {$ticker['BNBBTC']} BTC.\n";

// Get all of your positions, including estimated BTC value
$balances = $api->balances($ticker);
print_r($balances);

// Get all bid/ask prices
$bookPrices = $api->bookPrices();
print_r($bookPrices);
echo "BTC owned: ".$balances['BTC']['available']."\n";
echo "ETH owned: ".$balances['ETH']['available']."\n";
echo "Estimated Value: ".$api->btc_value." BTC\n";

// Place a LIMIT order
//$quantity = 1;
//$price = 0.0005;
//$order = $api->buy("BNBBTC", $quantity, $price);

// Place a MARKET order
//$quantity = 1;
//$order = $api->buy("BNBBTC", $quantity, 0, "MARKET");

// Get Trade History
//$trades = $api->trades("BNBBTC");
//print_r($trades);

// Get Market Depth
//$depth = $api->depth("ETHBTC");
//print_r($depth);

// Get Open Orders
//$openorders = $api->openOrders("BNBBTC");
//print_r($openorders);

// Get Order Status
//$orderid = "7610385";
//$orderstatus = $api->orderStatus("ETHBTC", $orderid);
//print_r($orderstatus);

// Cancel an Order
//$response = $api->cancel("ETHBTC", $orderid);
//print_r($response);

// Get all account orders; active, canceled, or filled.
//$orders = $api->trades("BNBBTC");
//print_r($orders);

// Get Kline/candlestick data for a symbol
// Periods: 1m,3m,5m,15m,30m,1h,2h,4h,6h,8h,12h,1d,3d,1w,1M
$ticks = $api->candlesticks("BNBBTC", "5m");
print_r($ticks);
