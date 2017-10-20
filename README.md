# PHP Binance API
This project is designed to help you make your own projects that interact with the [Binance API](https://www.binance.com/restapipub.html). You can stream candlestick chart data, market depth, or use other advanced features such as setting stop losses and iceberg orders. This project seeks to have complete API coverage including WebSockets.

#### Installation
```
composer require jaggedsoft/php-binance-api
```

#### Getting started
```php
<?php
require 'php-binance-api.php';
$api = new Binance("<api key>","<secret>");
```

#### Get latest price of a symbol
```php
$ticker = $api->prices();
print_r($ticker); // List prices of all symbols
echo "Price of BNB: {$ticker['BNBBTC']} BTC.\n";
```

#### Get all of your positions, including estimated BTC value
```php
$balances = $api->balances($ticker);
print_r($balances);
echo "BTC owned: ".$balances['BTC']['available']."\n";
echo "ETH owned: ".$balances['ETH']['available']."\n";
echo "Estimated Value: ".$api->btc_value." BTC\n";
```

#### Get all bid/ask prices
```php
$bookPrices = $api->bookPrices();
print_r($bookPrices);
```

#### Place a LIMIT order
```php
$quantity = 1;
$price = 0.0005;
$order = $api->buy("BNBBTC", $quantity, $price);
```

```php
$quantity = 1;
$price = 0.0006;
$order = $api->sell("BNBBTC", $quantity, $price);
```

#### Place a MARKET order
```php
$order = $api->buy("BNBBTC", $quantity, 0, "MARKET");
```

```php
$order = $api->sell("BNBBTC", $quantity, 0, "MARKET");
```

#### Get Trade History
```php
$trades = $api->trades("BNBBTC");
print_r($trades);
```

#### Get Market Depth
```php
$depth = $api->depth("ETHBTC");
print_r($depth);
```

#### Get Open Orders
```php
$openorders = $api->openOrders("BNBBTC");
print_r($openorders);
```

#### Get Order Status
```php
$orderid = "7610385";
$orderstatus = $api->orderStatus("ETHBTC", $orderid);
print_r($orderstatus);
```

#### Cancel an Order
```php
$response = $api->cancel("ETHBTC", $orderid);
print_r($response);
```

#### Get all account orders; active, canceled, or filled.
```php
$orders = $api->orders("BNBBTC");
print_r($orders);
```

#### Get Kline/candlestick data for a symbol
```php
//Periods: 1m,3m,5m,15m,30m,1h,2h,4h,6h,8h,12h,1d,3d,1w,1M
$ticks = $api->candlesticks("BNBBTC", "5m");
print_r($ticks);
```

## WebSocket API

#### Grab realtime updated depth cache via WebSockets
```php
$api->depthCache(["BNBBTC"], function($api, $symbol, $depth) {
    echo "{$symbol} depth cache update\n";
    $limit = 10; // Show only the 10 closest asks/bids
    $sorted = $api->sortDepth($symbol);
    $bid = $api->first($sorted['bids']);
    $ask = $api->first($sorted['asks']);
    $sorted['asks'] = array_reverse($sorted['asks']);
    print_r($sorted);
    echo "ask: {$ask}\n";
    echo "bid: {$bid}\n";
});
```
