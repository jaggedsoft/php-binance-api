<?php
require '../vendor/autoload.php';
$api = new Binance\API("<api key>","<secret>");

// Get latest price of all symbols
$tickers = $api->prices();
print_r($tickers); // List prices of all symbols

// Get latest price of a symbol
$price = $api->price('BNBBTC');
echo "Price of BNB: {$price} BTC.\n";

// Get all of your positions, including estimated BTC value
$balances = $api->balances($tickers);
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

// Place a STOP LOSS order
// When the stop is reached, a stop order becomes a market order
//$quantity = 1;
//$price = 0.5; // Try to sell it for 0.5 btc
//$stopPrice = 0.4; // Sell immediately if price goes below 0.4 btc
//$order = $api->sell("BNBBTC", $quantity, $price, "LIMIT", ["stopPrice"=>$stopPrice]);
//print_r($order);

// Place an ICEBERG order
// Iceberg orders are intended to conceal the true order quantity.
//$quantity = 1;
//$price = 0.5;
//$icebergQty = 10;
//$order = $api->sell("BNBBTC", $quantity, $price, "LIMIT", ["icebergQty"=>$icebergQty]);
//print_r($order);

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

// Get Trade History
//$history = $api->history("BNBBTC");
//print_r($history);

// Get Kline/candlestick data for a symbol
// Periods: 1m,3m,5m,15m,30m,1h,2h,4h,6h,8h,12h,1d,3d,1w,1M
$ticks = $api->candlesticks("BNBBTC", "5m");
print_r($ticks);

// Aggregate Trades List
//$trades = $api->aggTrades("BNBBTC");
//print_r($trades);

// Trade Updates via WebSocket
//$api->trades(["BNBBTC"], function($api, $symbol, $trades) {
//    echo "{$symbol} trades update".PHP_EOL;
//    print_r($trades);
//});


// Get complete realtime chart data via WebSockets
//$api->chart(["BNBBTC"], "15m", function($api, $symbol, $chart) {
//    echo "{$symbol} chart update\n";
//    print_r($chart);
//});


// Grab realtime updated depth cache via WebSockets
$api->depthCache(["BNBBTC"], function($api, $symbol, $depth) {
    echo "{$symbol} depth cache update\n";
    $limit = 11; // Show only the closest asks/bids
    $sorted = $api->sortDepth($symbol, $limit);
    $bid = $api->first($sorted['bids']);
    $ask = $api->first($sorted['asks']);
    echo $api->displayDepth($sorted);
    echo "ask: {$ask}\n";
    echo "bid: {$bid}\n";
});
