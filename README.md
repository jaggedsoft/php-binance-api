[![Complete coverage](https://camo.githubusercontent.com/f52b6b64144aedecb7596469a608ddf7982a5b01/68747470733a2f2f696d672e736869656c64732e696f2f636f766572616c6c732f726571756573742f726571756573742d70726f6d6973652e7376673f7374796c653d666c61742d737175617265266d61784167653d32353932303030)](#)
[![Dependencies up to date](https://camo.githubusercontent.com/895093f8ef43722ff6c4bd61cd720199e76de812/68747470733a2f2f696d672e736869656c64732e696f2f64617669642f726571756573742f726571756573742e7376673f7374796c653d666c61742d737175617265)](#)
[![GitHub last commit](https://img.shields.io/github/last-commit/jaggedsoft/php-binance-api.svg)](#)


# PHP Binance API
This project is designed to help you make your own projects that interact with the [Binance API](https://www.binance.com/restapipub.html). You can stream candlestick chart data, market depth, or use other advanced features such as setting stop losses and iceberg orders. This project seeks to have complete API coverage including WebSockets.

#### Installation
```
composer require jaggedsoft/php-binance-api
```
<details>
 <summary>Click for help with installation</summary>

## Install Composer
If the above step didn't work, install composer and try again.
#### Debian / Ubuntu
```
sudo apt-get install curl php5-cli git
curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer
```
Composer not found? Use this command instead:
```
php composer.phar require "jaggedsoft/php-binance-api @dev"
```

#### Windows:
[Download installer for Windows](https://getcomposer.org/doc/00-intro.md#installation-windows)



</details>

#### Getting started
```php
require 'vendor/autoload.php';
$api = new Binance\API("<api key>","<secret>");
```

#### Get latest price of a symbol
```php
$ticker = $api->prices();
print_r($ticker); // List prices of all symbols
echo "Price of BNB: {$ticker['BNBBTC']} BTC.".PHP_EOL;
```

#### Get balances for all of your positions, including estimated BTC value
```php
$balances = $api->balances($ticker);
print_r($balances);
echo "BTC owned: ".$balances['BTC']['available'].PHP_EOL;
echo "ETH owned: ".$balances['ETH']['available'].PHP_EOL;
echo "Estimated Value: ".$api->btc_value." BTC".PHP_EOL;
```

<details>
 <summary>View Response</summary>

```
    [WTC] => Array
        (
            [available] => 909.61000000
            [onOrder] => 0.00000000
            [btcValue] => 0.94015470
        )

    [BNB] => Array
        (
            [available] => 1045.94316876
            [onOrder] => 0.00000000
            [btcValue] => 0.21637426
        )
```
</details>


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
$quantity = 1;
$order = $api->buy("BNBBTC", $quantity, 0, "MARKET");
```

```php
$quantity = 0.01;
$order = $api->sell("ETHBTC", $quantity, 0, "MARKET");
```

#### Place a STOP LOSS order
```php
// When the stop is reached, a stop order becomes a market order
$quantity = 1;
$price = 0.5; // Try to sell it for 0.5 btc
$stopPrice = 0.4; // Sell immediately if price goes below 0.4 btc
$order = $api->sell("BNBBTC", $quantity, $price, "LIMIT", ["stopPrice"=>$stopPrice]);
print_r($order);
```

#### Place an ICEBERG order
```php
// Iceberg orders are intended to conceal the true order quantity.
$quantity = 1;
$price = 0.5;
$icebergQty = 10;
$order = $api->sell("BNBBTC", $quantity, $price, "LIMIT", ["icebergQty"=>$icebergQty]);
print_r($order);
```

#### Complete Trade History
```php
$history = $api->history("BNBBTC");
print_r($history);
```

<details>
 <summary>View Response</summary>

```
Array (
    [0] => Array (
            [id] => 831585
            [orderId] => 3635308
            [price] => 0.00028800
            [qty] => 4.00000000
            [commission] => 0.00200000
            [commissionAsset] => BNB
            [time] => 1504805561369
            [isBuyer] => 1
            [isMaker] =>
            [isBestMatch] => 1
        )

    [1] => Array (
            [id] => 1277334
            [orderId] => 6126625
            [price] => 0.00041054
            [qty] => 16.00000000
            [commission] => 0.00800000
            [commissionAsset] => BNB
            [time] => 1507059468604
            [isBuyer] => 1
            [isMaker] =>
            [isBestMatch] => 1
        )

    [2] => Array (
            [id] => 1345995
            [orderId] => 6407202
            [price] => 0.00035623
            [qty] => 30.00000000
            [commission] => 0.01500000
            [commissionAsset] => BNB
            [time] => 1507434311489
            [isBuyer] => 1
            [isMaker] => 1
            [isBestMatch] => 1
        )
)
```
</details>

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

#### Aggregate Trades List
```php
$trades = $api->aggTrades("BNBBTC");
print_r($trades);
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
<details>
 <summary>View Response</summary>

```
   [1508560200000] => Array
        (
            [open] => 0.00019691
            [high] => 0.00019695
            [low] => 0.00019502
            [close] => 0.00019503
            [volume] => 0.13712290
        )

    [1508560500000] => Array
        (
            [open] => 0.00019502
            [high] => 0.00019693
            [low] => 0.00019501
            [close] => 0.00019692
            [volume] => 1.03216357
        )

    [1508560800000] => Array
        (
            [open] => 0.00019692
            [high] => 0.00019692
            [low] => 0.00019689
            [close] => 0.00019692
            [volume] => 0.22270990
        )
```
</details>

## WebSocket API

#### Realtime Chart Cache via WebSockets
```php
$api->chart(["BNBBTC"], "15m", function($api, $symbol, $chart) {
    echo "{$symbol} chart update\n";
    print_r($chart);
});
```
<details>
 <summary>View Response</summary>

```
   [1508560200000] => Array
        (
            [open] => 0.00019691
            [high] => 0.00019695
            [low] => 0.00019502
            [close] => 0.00019503
            [volume] => 0.13712290
        )

    [1508560500000] => Array
        (
            [open] => 0.00019502
            [high] => 0.00019693
            [low] => 0.00019501
            [close] => 0.00019692
            [volume] => 1.03216357
        )

    [1508560800000] => Array
        (
            [open] => 0.00019692
            [high] => 0.00019692
            [low] => 0.00019689
            [close] => 0.00019692
            [volume] => 0.22270990
        )
```
</details>


#### Trade Updates via WebSocket
```php
$api->trades(["BNBBTC"], function($api, $symbol, $trades) {
    echo "{$symbol} trades update".PHP_EOL;
    print_r($trades);
});
```


#### Realtime updated depth cache via WebSockets
```php
$api->depthCache(["BNBBTC"], function($api, $symbol, $depth) {
	echo "{$symbol} depth cache update".PHP_EOL;
	//print_r($depth); // Print all depth data
	$limit = 11; // Show only the closest asks/bids
	$sorted = $api->sortDepth($symbol, $limit);
	$bid = $api->first($sorted['bids']);
	$ask = $api->first($sorted['asks']);
	echo $api->displayDepth($sorted);
	echo "ask: {$ask}".PHP_EOL;
	echo "bid: {$bid}".PHP_EOL;
});
```
<details>
 <summary>View Response</summary>

```
asks:
0.00020649      1,194      0.24654906
0.00020600        375      0.07725000
0.00020586          4      0.00823440
0.00020576          1      0.00205760
0.00020564        226      0.04647464
0.00020555         38      0.00781090
0.00020552         98      0.02014096
0.00020537        121      0.02484977
0.00020520         46      0.09439200
0.00020519         29      0.05950510
0.00020518        311      0.06381098
bids:
0.00022258      5,142      1.14450636
0.00020316          7      0.00142212
0.00020315         82      0.01665830
0.00020314         16      0.00325024
0.00020313        512      0.10400256
0.00020238          5      0.01011900
0.00020154      1,207      0.24325878
0.00020151          1      0.02015100
0.00020150          3      0.60450000
0.00020140        217      0.04370380
0.00020135          1      0.02013500
ask: 0.00020518
bid: 0.00022258

```
</details>
