[![Latest Version](https://img.shields.io/github/release/jaggedsoft/php-binance-api.svg?style=flat-square)](https://github.com/jaggedsoft/php-binance-api/releases) 
[![GitHub last commit](https://img.shields.io/github/last-commit/jaggedsoft/php-binance-api.svg?style=flat-square)](#) 
[![HitCount](http://hits.dwyl.io/jaggedsoft/php-binance-api.svg)](http://hits.dwyl.io/jaggedsoft/php-binance-api) 
[![Packagist Downloads](https://img.shields.io/packagist/dt/jaggedsoft/php-binance-api.svg?style=flat-square)](https://packagist.org/packages/jaggedsoft/php-binance-api) 


[![Build Status](https://travis-ci.org/jaggedsoft/php-binance-api.svg?branch=master&style=flat-square)](https://travis-ci.org/jaggedsoft/php-binance-api) 
[![Coverage Status](https://coveralls.io/repos/github/jaggedsoft/php-binance-api/badge.svg?branch=master&style=flat-square)](https://coveralls.io/github/jaggedsoft/php-binance-api) 
[![CodeCov](https://codecov.io/gh/jaggedsoft/php-binance-api/branch/master/graph/badge.svg?style=flat-square)](https://codecov.io/github/jaggedsoft/php-binance-api/) 
[![Codacy Badge](https://api.codacy.com/project/badge/Coverage/683459a5a71c4875956cf23078a0c39b)](https://www.codacy.com/app/dmzoneill/php-binance-api?utm_source=github.com&utm_medium=referral&utm_content=jaggedsoft/php-binance-api&utm_campaign=Badge_Coverage)
[![Code consistency](https://squizlabs.github.io/PHP_CodeSniffer/analysis/jaggedsoft/php-binance-api/grade.svg?style=flat-square)](https://squizlabs.github.io/PHP_CodeSniffer/analysis/jaggedsoft/php-binance-api)
[![Codacy Badge](https://api.codacy.com/project/badge/Grade/683459a5a71c4875956cf23078a0c39b)](https://www.codacy.com/app/dmzoneill/php-binance-api?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=jaggedsoft/php-binance-api&amp;utm_campaign=Badge_Grade)

# PHP Binance API
This project is designed to help you make your own projects that interact with the [Binance API](https://github.com/binance-exchange/binance-official-api-docs). You can stream candlestick chart data, market depth, or use other advanced features such as setting stop losses and iceberg orders. This project seeks to have complete API coverage including WebSockets.

> Special thank you to all contributors: **dmzoneill, dxjones, jokaorgua,** and others!! *This package needs help from the community.* Improvements contributed to this project are encouraged, and you will be given full credit for changes. All pull requests welcome.

#### Installation
```
composer require "jaggedsoft/php-binance-api @dev"
```
<details>
 <summary>Click for help with installation</summary>

## Install Composer
If the above step didn't work, install composer and try again.
#### Debian / Ubuntu
```
sudo apt-get install curl
curl -s http://getcomposer.org/installer | php
php composer.phar install
```
Composer not found? Use this command instead:
```
php composer.phar require "jaggedsoft/php-binance-api @dev"
```

#### Windows:
[Download installer for Windows](https://github.com/jaggedsoft/php-binance-api/#installing-on-windows)

</details>

#### Getting started
`composer require jaggedsoft/php-binance-api`
```php
require 'vendor/autoload.php';
// 1. config in home directory
$api = new Binance\API();
// 2. config in specified file
$api = new Binance\API( "somefile.json" );
// 3. config by specifying api key and secret
$api = new Binance\API("<api key>","<secret>");
// 4. Rate Limiting Support
$api = new Binance\RateLimiter(new Binance\API());
```
See [additional options](https://github.com/jaggedsoft/php-binance-api/#config-file-in-home-directory) for more options and help installing on Windows

#### Rate Limiting
This Feature is in beta, you can start using rate limiting as a wrapper to the main API class.
```php
$api = new Binance\API( "somefile.json" );
$api = new Binance\RateLimiter($api);
while(true) {
   $api->openOrders("BNBBTC"); // rate limited
}
```

#### Security - Ca Bunldes
If you don't know what a CA bundle is, no action is required.  If you do know and you don't like our auto upate feature.
You can disable the downloading of the CA Bundle
```php
$api = new Binance\API( "somefile.json" );
$api->caOverride = true;
```

#### Get latest price of all symbols
```php
$ticker = $api->prices();
print_r($ticker);
```

<details>
 <summary>View Response</summary>

```
Array
(
    [ETHBTC] => 0.05050800
    [LTCBTC] => 0.00967400
    [BNBBTC] => 0.00021479
    [NEOBTC] => 0.00479300
    [123456] => 0.00030000
    [QTUMETH] => 0.03482000
    [EOSETH] => 0.00176100
    [SNTETH] => 0.00008766
    [BNTETH] => 0.00662400
    [BCCBTC] => 0.05629200
    [GASBTC] => 0.00338500
    [BNBETH] => 0.00418603
    [BTMETH] => 0.00018900
    [HCCBTC] => 0.00000180
    [BTCUSDT] => 6028.95000000
    [ETHUSDT] => 304.98000000
    [HSRBTC] => 0.00289000
    [OAXETH] => 0.00136700
    [DNTETH] => 0.00020573
    [MCOETH] => 0.02685800
    [ICNETH] => 0.00395000
    [ELCBTC] => 0.00000053
    [MCOBTC] => 0.00133000
    [WTCBTC] => 0.00117000
    [WTCETH] => 0.02300000
    [LLTBTC] => 0.00001669
    [LRCBTC] => 0.00001100
    [LRCETH] => 0.00016311
    [QTUMBTC] => 0.00178400
    [YOYOBTC] => 0.00000481
    [OMGBTC] => 0.00125600
    [OMGETH] => 0.02497000
    [ZRXBTC] => 0.00003376
    [ZRXETH] => 0.00067001
    [STRATBTC] => 0.00052100
    [STRATETH] => 0.00950200
    [SNGLSBTC] => 0.00002216
    [SNGLSETH] => 0.00043508
    [BQXBTC] => 0.00010944
    [BQXETH] => 0.00241250
    [KNCBTC] => 0.00017060
    [KNCETH] => 0.00340090
    [FUNBTC] => 0.00000313
    [FUNETH] => 0.00006184
    [SNMBTC] => 0.00001761
    [SNMETH] => 0.00035599
    [NEOETH] => 0.09500000
    [IOTABTC] => 0.00006783
    [IOTAETH] => 0.00136000
    [LINKBTC] => 0.00004476
    [LINKETH] => 0.00087796
    [XVGBTC] => 0.00000081
    [XVGETH] => 0.00001611
    [CTRBTC] => 0.00009408
    [CTRETH] => 0.00187010
    [SALTBTC] => 0.00044400
    [SALTETH] => 0.00890000
    [MDABTC] => 0.00021973
    [MDAETH] => 0.00435550
    [MTLBTC] => 0.00116900
    [MTLETH] => 0.02470000
    [SUBBTC] => 0.00002163
    [SUBETH] => 0.00042901
    [EOSBTC] => 0.00008822
    [SNTBTC] => 0.00000438
    [ETC] => 0.00000000
    [ETCETH] => 0.03600000
    [ETCBTC] => 0.00180800
    [MTHBTC] => 0.00001425
    [MTHETH] => 0.00028092
    [ENGBTC] => 0.00007040
    [ENGETH] => 0.00138220
    [DNTBTC] => 0.00001052
    [ZECBTC] => 0.00000000
    [ZECETH] => 0.00000000
    [BNTBTC] => 0.00033501
    [ASTBTC] => 0.00004528
    [ASTETH] => 0.00083990
    [DASHBTC] => 0.04651300
    [DASHETH] => 0.90520000
)
Price of BNB: 0.00021479 BTC.
```
</details>

#### Get latest price of a symbol
```php
$price = $api->price("BNBBTC");
echo "Price of BNB: {$price} BTC.".PHP_EOL;
```

#### Get miniTicker for all symbols
```php
$api->miniTicker(function($api, $ticker) {
	print_r($ticker);
});
```

<details>
 <summary>View Response</summary>

```
    [7] => Array
        (
            [symbol] => LTCUSDT
            [close] => 182.85000000
            [open] => 192.62000000
            [high] => 195.25000000
            [low] => 173.08000000
            [volume] => 238603.66451000
            [quoteVolume] => 43782422.11276660
            [eventTime] => 1520497914289
        )

    [8] => Array
        (
            [symbol] => ICXBTC
            [close] => 0.00029790
            [open] => 0.00030550
            [high] => 0.00031600
            [low] => 0.00026850
            [volume] => 8468620.53000000
            [quoteVolume] => 2493.60935828
            [eventTime] => 1520497915200
        )

```
</details>

#### Get balances for all of your positions, including estimated BTC value
```php
$ticker = $api->prices(); // Make sure you have an updated ticker object for this to work
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
... (more)
```
</details>


#### Get all bid/ask prices
```php
$bookPrices = $api->bookPrices();
print_r($bookPrices);
echo "Bid price of BNB: {$bookPrices['BNBBTC']['bid']}".PHP_EOL;
```

<details>
 <summary>View Response</summary>

```
Price of BNB: 0.00021491

Array
(
    [ETHBTC] => Array
        (
            [bid] => 0.05053000
            [bids] => 7.21000000
            [ask] => 0.05076000
            [asks] => 13.73600000
        )
    [LTCBTC] => Array
        (
            [bid] => 0.00966500
            [bids] => 62.57000000
            [ask] => 0.00967100
            [asks] => 5.48000000
        )
    [BNBBTC] => Array
        (
            [bid] => 0.00021010
            [bids] => 6.00000000
            [ask] => 0.00021479
            [asks] => 76.00000000
        )
    [NEOBTC] => Array
        (
            [bid] => 0.00476600
            [bids] => 5.16000000
            [ask] => 0.00479900
            [asks] => 276.00000000
        )
    [QTUMETH] => Array
        (
            [bid] => 0.03515000
            [bids] => 11.87000000
            [ask] => 0.03599900
            [asks] => 0.60000000
        )
    [EOSETH] => Array
        (
            [bid] => 0.00176000
            [bids] => 52.63000000
            [ask] => 0.00177900
            [asks] => 654.44000000
        )
    [SNTETH] => Array
        (
            [bid] => 0.00008522
            [bids] => 2347.00000000
            [ask] => 0.00008764
            [asks] => 2151.00000000
        )
    [BNTETH] => Array
        (
            [bid] => 0.00662400
            [bids] => 1940.32000000
            [ask] => 0.00683900
            [asks] => 64.89000000
        )
    [BCCBTC] => Array
        (
            [bid] => 0.05614300
            [bids] => 2.15000000
            [ask] => 0.05710000
            [asks] => 0.75900000
        )
    [GASBTC] => Array
        (
            [bid] => 0.00337800
            [bids] => 597.29000000
            [ask] => 0.00338500
            [asks] => 14.63000000
        )
    [BNBETH] => Array
        (
            [bid] => 0.00411497
            [bids] => 375.00000000
            [ask] => 0.00418603
            [asks] => 4.00000000
        )
    [BTMETH] => Array
        (
            [bid] => 0.00000000
            [bids] => 0.00000000
            [ask] => 0.00000000
            [asks] => 0.00000000
        )
    [HCCBTC] => Array
        (
            [bid] => 0.00000000
            [bids] => 0.00000000
            [ask] => 0.00000000
            [asks] => 0.00000000
        )
    [BTCUSDT] => Array
        (
            [bid] => 5970.00000000
            [bids] => 0.00500000
            [ask] => 5989.96000000
            [asks] => 0.26295200
        )
    [ETHUSDT] => Array
        (
            [bid] => 303.86000000
            [bids] => 4.27000000
            [ask] => 304.99000000
            [asks] => 0.11361000
        )
    [HSRBTC] => Array
        (
            [bid] => 0.00000000
            [bids] => 0.00000000
            [ask] => 0.00000000
            [asks] => 0.00000000
        )
    [OAXETH] => Array
        (
            [bid] => 0.00137100
            [bids] => 145.88000000
            [ask] => 0.00139500
            [asks] => 960.81000000
        )
    [DNTETH] => Array
        (
            [bid] => 0.00020421
            [bids] => 19401.00000000
            [ask] => 0.00020573
            [asks] => 1.00000000
        )
    [MCOETH] => Array
        (
            [bid] => 0.02630000
            [bids] => 20.36000000
            [ask] => 0.02684100
            [asks] => 75.35000000
        )
    [ICNETH] => Array
        (
            [bid] => 0.00391600
            [bids] => 51.07000000
            [ask] => 0.00396800
            [asks] => 146.69000000
        )
    [ELCBTC] => Array
        (
            [bid] => 0.00000000
            [bids] => 0.00000000
            [ask] => 0.00000000
            [asks] => 0.00000000
        )
    [MCOBTC] => Array
        (
            [bid] => 0.00132800
            [bids] => 24.64000000
            [ask] => 0.00133200
            [asks] => 8.26000000
        )
    [WTCBTC] => Array
        (
            [bid] => 0.00116640
            [bids] => 104.00000000
            [ask] => 0.00118000
            [asks] => 1572.00000000
        )
    [WTCETH] => Array
        (
            [bid] => 0.02311400
            [bids] => 0.99000000
            [ask] => 0.02330000
            [asks] => 27.68000000
        )
    [LLTBTC] => Array
        (
            [bid] => 0.00000000
            [bids] => 0.00000000
            [ask] => 0.00000000
            [asks] => 0.00000000
        )
    [LRCBTC] => Array
        (
            [bid] => 0.00000000
            [bids] => 0.00000000
            [ask] => 0.00000000
            [asks] => 0.00000000
        )
    [LRCETH] => Array
        (
            [bid] => 0.00000000
            [bids] => 0.00000000
            [ask] => 0.00000000
            [asks] => 0.00000000
        )
    [QTUMBTC] => Array
        (
            [bid] => 0.00178700
            [bids] => 328.30000000
            [ask] => 0.00180500
            [asks] => 50.00000000
        )
    [YOYOBTC] => Array
        (
            [bid] => 0.00000000
            [bids] => 0.00000000
            [ask] => 0.00000000
            [asks] => 0.00000000
        )
    [OMGBTC] => Array
        (
            [bid] => 0.00126100
            [bids] => 61.00000000
            [ask] => 0.00126400
            [asks] => 8.50000000
        )
    [OMGETH] => Array
        (
            [bid] => 0.02467200
            [bids] => 60.99000000
            [ask] => 0.02527500
            [asks] => 7.98000000
        )
    [ZRXBTC] => Array
        (
            [bid] => 0.00003370
            [bids] => 69.00000000
            [ask] => 0.00003377
            [asks] => 7437.00000000
        )
    [ZRXETH] => Array
        (
            [bid] => 0.00065565
            [bids] => 68.00000000
            [ask] => 0.00069171
            [asks] => 123.00000000
        )
    [STRATBTC] => Array
        (
            [bid] => 0.00051200
            [bids] => 387.00000000
            [ask] => 0.00052100
            [asks] => 17.90000000
        )
    [STRATETH] => Array
        (
            [bid] => 0.00988800
            [bids] => 299.97000000
            [ask] => 0.01084600
            [asks] => 133.91000000
        )
    [SNGLSBTC] => Array
        (
            [bid] => 0.00002211
            [bids] => 1028.00000000
            [ask] => 0.00002217
            [asks] => 536.00000000
        )
    [SNGLSETH] => Array
        (
            [bid] => 0.00043801
            [bids] => 892.00000000
            [ask] => 0.00043902
            [asks] => 1585.00000000
        )
    [BQXBTC] => Array
        (
            [bid] => 0.00011061
            [bids] => 1814.00000000
            [ask] => 0.00011496
            [asks] => 1707.00000000
        )
    [BQXETH] => Array
        (
            [bid] => 0.00220610
            [bids] => 109.00000000
            [ask] => 0.00241190
            [asks] => 2606.00000000
        )
    [KNCBTC] => Array
        (
            [bid] => 0.00017061
            [bids] => 1109.00000000
            [ask] => 0.00017297
            [asks] => 63.00000000
        )
    [KNCETH] => Array
        (
            [bid] => 0.00340090
            [bids] => 3.00000000
            [ask] => 0.00342860
            [asks] => 515.00000000
        )
    [FUNBTC] => Array
        (
            [bid] => 0.00000314
            [bids] => 17100.00000000
            [ask] => 0.00000317
            [asks] => 15600.00000000
        )
    [FUNETH] => Array
        (
            [bid] => 0.00006186
            [bids] => 4473.00000000
            [ask] => 0.00006467
            [asks] => 42036.00000000
        )
    [SNMBTC] => Array
        (
            [bid] => 0.00001760
            [bids] => 3695.00000000
            [ask] => 0.00001781
            [asks] => 623.00000000
        )
    [SNMETH] => Array
        (
            [bid] => 0.00034783
            [bids] => 507.00000000
            [ask] => 0.00035350
            [asks] => 1501.00000000
        )
    [NEOETH] => Array
        (
            [bid] => 0.09414500
            [bids] => 12.38000000
            [ask] => 0.09599700
            [asks] => 23.38000000
        )
    [IOTABTC] => Array
        (
            [bid] => 0.00006791
            [bids] => 2000.00000000
            [ask] => 0.00006857
            [asks] => 1861.00000000
        )
    [IOTAETH] => Array
        (
            [bid] => 0.00135101
            [bids] => 1461.00000000
            [ask] => 0.00138938
            [asks] => 21.00000000
        )
    [LINKBTC] => Array
        (
            [bid] => 0.00004400
            [bids] => 683.00000000
            [ask] => 0.00004491
            [asks] => 7292.00000000
        )
    [LINKETH] => Array
        (
            [bid] => 0.00086045
            [bids] => 682.00000000
            [ask] => 0.00087683
            [asks] => 4286.00000000
        )
    [XVGBTC] => Array
        (
            [bid] => 0.00000080
            [bids] => 96600.00000000
            [ask] => 0.00000081
            [asks] => 179622.00000000
        )
    [XVGETH] => Array
        (
            [bid] => 0.00001556
            [bids] => 96537.00000000
            [ask] => 0.00001675
            [asks] => 4.00000000
        )
    [CTRBTC] => Array
        (
            [bid] => 0.00009346
            [bids] => 2133.00000000
            [ask] => 0.00009470
            [asks] => 1992.00000000
        )
    [CTRETH] => Array
        (
            [bid] => 0.00187050
            [bids] => 501.00000000
            [ask] => 0.00189230
            [asks] => 105.00000000
        )
    [SALTBTC] => Array
        (
            [bid] => 0.00044400
            [bids] => 181.09000000
            [ask] => 0.00044700
            [asks] => 1144.81000000
        )
    [SALTETH] => Array
        (
            [bid] => 0.00866500
            [bids] => 216.71000000
            [ask] => 0.00893900
            [asks] => 237.00000000
        )
    [MDABTC] => Array
        (
            [bid] => 0.00021328
            [bids] => 555.00000000
            [ask] => 0.00021973
            [asks] => 236.00000000
        )
    [MDAETH] => Array
        (
            [bid] => 0.00425610
            [bids] => 450.00000000
            [ask] => 0.00441450
            [asks] => 511.00000000
        )
    [MTLBTC] => Array
        (
            [bid] => 0.00114500
            [bids] => 194.48000000
            [ask] => 0.00117000
            [asks] => 1.40000000
        )
    [MTLETH] => Array
        (
            [bid] => 0.02156000
            [bids] => 183.00000000
            [ask] => 0.02436700
            [asks] => 200.97000000
        )
    [SUBBTC] => Array
        (
            [bid] => 0.00002116
            [bids] => 520.00000000
            [ask] => 0.00002177
            [asks] => 957.00000000
        )
    [SUBETH] => Array
        (
            [bid] => 0.00042121
            [bids] => 202.00000000
            [ask] => 0.00044390
            [asks] => 69.00000000
        )
    [EOSBTC] => Array
        (
            [bid] => 0.00008837
            [bids] => 52.00000000
            [ask] => 0.00008901
            [asks] => 565.00000000
        )
    [SNTBTC] => Array
        (
            [bid] => 0.00000431
            [bids] => 11731.00000000
            [ask] => 0.00000439
            [asks] => 9000.00000000
        )
    [ETC] => Array
        (
            [bid] => 0.00000000
            [bids] => 0.00000000
            [ask] => 0.00000000
            [asks] => 0.00000000
        )
    [ETCETH] => Array
        (
            [bid] => 0.03600000
            [bids] => 460.15000000
            [ask] => 0.03699600
            [asks] => 30.00000000
        )
    [ETCBTC] => Array
        (
            [bid] => 0.00181200
            [bids] => 6.90000000
            [ask] => 0.00183700
            [asks] => 2.72000000
        )
    [MTHBTC] => Array
        (
            [bid] => 0.00001400
            [bids] => 400.00000000
            [ask] => 0.00001467
            [asks] => 615.00000000
        )
    [MTHETH] => Array
        (
            [bid] => 0.00027316
            [bids] => 399.00000000
            [ask] => 0.00029096
            [asks] => 24939.00000000
        )
    [ENGBTC] => Array
        (
            [bid] => 0.00006927
            [bids] => 2896.00000000
            [ask] => 0.00007040
            [asks] => 75.00000000
        )
    [ENGETH] => Array
        (
            [bid] => 0.00138220
            [bids] => 1111.00000000
            [ask] => 0.00142990
            [asks] => 2010.00000000
        )
    [DNTBTC] => Array
        (
            [bid] => 0.00001053
            [bids] => 11295.00000000
            [ask] => 0.00001065
            [asks] => 8272.00000000
        )
    [ZECBTC] => Array
        (
            [bid] => 0.00000000
            [bids] => 0.00000000
            [ask] => 0.00000000
            [asks] => 0.00000000
        )
    [ZECETH] => Array
        (
            [bid] => 0.00000000
            [bids] => 0.00000000
            [ask] => 0.00000000
            [asks] => 0.00000000
        )
    [BNTBTC] => Array
        (
            [bid] => 0.00033500
            [bids] => 15.00000000
            [ask] => 0.00033996
            [asks] => 679.00000000
        )
    [ASTBTC] => Array
        (
            [bid] => 0.00004133
            [bids] => 9513.00000000
            [ask] => 0.00004528
            [asks] => 4170.00000000
        )
    [ASTETH] => Array
        (
            [bid] => 0.00083830
            [bids] => 4296.00000000
            [ask] => 0.00084900
            [asks] => 999.00000000
        )
    [DASHBTC] => Array
        (
            [bid] => 0.04651200
            [bids] => 0.25000000
            [ask] => 0.04659000
            [asks] => 1.00000000
        )
    [DASHETH] => Array
        (
            [bid] => 0.90420000
            [bids] => 63.96400000
            [ask] => 0.94375000
            [asks] => 0.36900000
        )
)
```
</details>

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
$order = $api->marketBuy("BNBBTC", $quantity);
```

```php
$quantity = 0.01;
$order = $api->marketSell("ETHBTC", $quantity);
```

<details>
 <summary>View Response</summary>

```
(
    [symbol] => BNBBTC
    [orderId] => 7652393
    [clientOrderId] => aAE7BNUhITQj3eg04iG1sY
    [transactTime] => 1508564815865
    [price] => 0.00000000
    [origQty] => 1.00000000
    [executedQty] => 1.00000000
    [status] => FILLED
    [timeInForce] => GTC
    [type] => MARKET
    [side] => BUY
)
```
</details>


#### Place a STOP LOSS order
```php
// When the stop is reached, a stop order becomes a market order
$type = "STOP_LOSS"; // Set the type STOP_LOSS (market) or STOP_LOSS_LIMIT, and TAKE_PROFIT (market) or TAKE_PROFIT_LIMIT
$quantity = 1;
$price = 0.5; // Try to sell it for 0.5 btc
$stopPrice = 0.4; // Sell immediately if price goes below 0.4 btc
$order = $api->sell("BNBBTC", $quantity, $price, $type, ["stopPrice"=>$stopPrice]);
print_r($order);
```

#### Place an ICEBERG order
```php
// Iceberg orders are intended to conceal the true order quantity.
$type = "LIMIT"; // LIMIT, STOP_LOSS_LIMIT, and TAKE_PROFIT_LIMIT
$quantity = 1;
$price = 0.5;
$icebergQty = 10;
$order = $api->sell("BNBBTC", $quantity, $price, $type, ["icebergQty"=>$icebergQty]);
print_r($order);
```

#### Getting 24hr ticker price change statistics for a symbol
```php
$prevDay = $api->prevDay("BNBBTC");
print_r($prevDay);
echo "BNB price change since yesterday: ".$prevDay['priceChangePercent']."%".PHP_EOL;
```

#### Complete Account Trade History
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
... (more)
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

#### Market History / Aggregate Trades
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
... (more)
```
</details>

## WebSocket API

#### miniTicker return the latest candlestick information for every symbol
```php
$api->miniTicker(function($api, $ticker) {
	print_r($ticker);
});
```

<details>
 <summary>View Response</summary>

```
    [18] => Array
        (
            [symbol] => ONTBNB
            [close] => 0.37649000
            [open] => 0.30241000
            [high] => 0.38112000
            [low] => 0.29300000
            [volume] => 975240.72000000
            [quoteVolume] => 326908.77744250
            [eventTime] => 1523395389582
        )

    [19] => Array
        (
            [symbol] => WANBTC
            [close] => 0.00063657
            [open] => 0.00054151
            [high] => 0.00063900
            [low] => 0.00053900
            [volume] => 4443618.00000000
            [quoteVolume] => 2637.76413131
            [eventTime] => 1523395389551
        )
```
</details>

#### Realtime Complete Chart Updates via WebSockets
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
... (more)
```
</details>

#### Get latest candlestick data only
```php
$api->kline(["BTCUSDT", "EOSBTC"], "5m", function($api, $symbol, $chart) {
  //echo "{$symbol} ({$interval}) candlestick update\n";
	$interval = $chart->i;
	$tick = $chart->t;
	$open = $chart->o;
	$high = $chart->h;
	$low = $chart->l;
	$close = $chart->c;
	$volume = $chart->q; // +trades buyVolume assetVolume makerVolume
	echo "{$symbol} price: {$close}\t volume: {$volume}\n";
});
```

#### Trade Updates via WebSocket
```php
$api->trades(["BNBBTC"], function($api, $symbol, $trades) {
	echo "{$symbol} trades update".PHP_EOL;
	print_r($trades);
});
```

#### Get ticker updates for all symbols via WebSocket
```php
$api->ticker(false, function($api, $symbol, $ticker) {
	print_r($ticker);
});
```

#### Get ticker updates for a specific symbol via WebSocket
```php
$api->ticker("BNBBTC", function($api, $symbol, $ticker) {
	print_r($ticker);
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

#### User Data: Account Balance Updates, Trade Updates, New Orders, Filled Orders, Cancelled Orders via WebSocket
```php
$balance_update = function($api, $balances) {
	print_r($balances);
	echo "Balance update".PHP_EOL;
};

$order_update = function($api, $report) {
	echo "Order update".PHP_EOL;
	print_r($report);
	$price = $report['price'];
	$quantity = $report['quantity'];
	$symbol = $report['symbol'];
	$side = $report['side'];
	$orderType = $report['orderType'];
	$orderId = $report['orderId'];
	$orderStatus = $report['orderStatus'];
	$executionType = $report['orderStatus'];
	if ( $executionType == "NEW" ) {
		if ( $executionType == "REJECTED" ) {
			echo "Order Failed! Reason: {$report['rejectReason']}".PHP_EOL;
		}
		echo "{$symbol} {$side} {$orderType} ORDER #{$orderId} ({$orderStatus})".PHP_EOL;
		echo "..price: {$price}, quantity: {$quantity}".PHP_EOL;
		return;
	}
	//NEW, CANCELED, REPLACED, REJECTED, TRADE, EXPIRED
	echo "{$symbol} {$side} {$executionType} {$orderType} ORDER #{$orderId}".PHP_EOL;
};
$api->userData($balance_update, $order_update);
```

<details>
 <summary>View Response</summary>

```
Order update
    [symbol] => BNBETH
    [side] => BUY
    [orderType] => LIMIT
    [quantity] => 2.00000000
    [price] => 0.00623005
    [executionType] => NEW
    [orderStatus] => NEW
    [rejectReason] => NONE
    [orderId] => 4102532
    [clientOrderId] => ULtH25RPmICFH0jvsQiq8y
    [orderTime] => 1508637831437
    [eventTime] => 1508637831440

BNBETH BUY LIMIT ORDER #4102532 (NEW)
..price: 0.00623005, quantity: 2.00000000

Balance update
    [BTC] => Array
        (
            [available] => 0.18167974
            [onOrder] => 0.00000000
        )

    [LTC] => Array
        (
            [available] => 0.00000000
            [onOrder] => 0.00000000
        )

    [ETH] => Array
        (
            [available] => 26.68739238
            [onOrder] => 2.55103500
        )
...(more)
```
</details>


#### Withdraw
```php
$asset = "BTC";
$address = "1C5gqLRs96Xq4V2ZZAR1347yUCpHie7sa";
$amount = 0.2;
$response = $api->withdraw($asset, $address, $amount);
print_r($response);
```

#### Withdraw with addressTag
```php
//Required for coins like XMR, XRP, etc.
$address = "44tLjmXrQNrWJ5NBsEj2R77ZBEgDa3fEe9GLpSf2FRmhexPvfYDUAB7EXX1Hdb3aMQ9FLqdJ56yaAhiXoRsceGJCRS3Jxkn";
$addressTag = "0e5e38a01058dbf64e53a4333a5acf98e0d5feb8e523d32e3186c664a9c762c1
";
$amount = 0.1;
$response = $api->withdraw($asset, $address, $amount, $addressTag);
print_r($response);
```

#### Get All Withdraw History
```php
$withdrawHistory = $api->withdrawHistory();
print_r($withdrawHistory);
```

#### Get Withdraw History for a specific asset
```php
$withdrawHistory = $api->withdrawHistory("BTC");
print_r($withdrawHistory);
```

#### Get Deposit Address
```php
$depositAddress = $api->depositAddress("VEN");
print_r($depositAddress);
```

#### Get All Deposit History
```php
$depositHistory = $api->depositHistory();
print_r($depositHistory);
```

### Troubleshooting
If you get the following errors, please synchronize your system time.
```
signedRequest error: {"code":-1021,"msg":"Timestamp for this request was 1000ms ahead of the server's time."}
signedRequest error: {"code":-1021,"msg":"Timestamp for this request is outside of the recvWindow."}
balanceData error: Please make sure your system time is synchronized, or pass the useServerTime option.
```

#### useServerTime 
```php
//Call this before running any functions
$api->useServerTime();
```

#### Installing on Windows
Download and install composer:
1. https://getcomposer.org/download/
2. Create a folder on your drive like C:\Binance
3. Run command prompt and type `cd C:\Binance`
4. ```composer require jaggedsoft/php-binance-api```
5. Once complete copy the vendor folder into your project.

#### Config file in home directory
If you dont wish to store your API key and secret in your scripts, load it from your home directory
```bash
mkdir -vp ~/.config/jaggedsoft/
cat >  ~/.config/jaggedsoft/php-binance-api.json << EOF
{
    "api-key": "<api key>",
    "api-secret": "<secret>"
}
EOF
```

#### Config file in home directory with curl options
```bash
mkdir -vp ~/.config/jaggedsoft/
cat >  ~/.config/jaggedsoft/php-binance-api.json << EOF
{
    "api-key": "<api key>",
    "api-secret": "<secret>",
    "curlOpts": {
	    "CURLOPT_SSL_VERIFYPEER": 0,
	    "INVALID_CONSTANT_NAME": 42
    }

}
EOF
```


Optionally add proxy configuration
```bash
mkdir -vp ~/.config/jaggedsoft/
cat >  ~/.config/jaggedsoft/php-binance-api.json << EOF
{
    "api-key": "<api key>",
    "api-secret": "<secret>",
    "proto": "https",
    "address": "proxy.domain.com",
    "port": "1080"
}
EOF
```

custom location
```php
$api = new Binance\API( "myfile.json" );
```


#### Basic stats: Get api call counter
```php
$api->getRequestCount();
```

#### Basic stats: Get total data transferred
```php
$api->getTransfered();
```



### Documentation
> There are also numerous other formats available here:
https://github.com/jaggedsoft/php-binance-api/tree/gh-pages
