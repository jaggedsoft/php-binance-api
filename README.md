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

#### Get all of your positions, including estimated BTC value
```php
$balances = $api->balances($ticker);
print_r($balances);
echo "BTC owned: ".$balances['BTC']['available'].PHP_EOL;
echo "ETH owned: ".$balances['ETH']['available'].PHP_EOL;
echo "Estimated Value: ".$api->btc_value." BTC".PHP_EOL;
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
$history = $api->history("BNBBTC");
print_r($history);
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

## WebSocket API

#### Realtime updated chart data via WebSockets


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
