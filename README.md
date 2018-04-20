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

#### Getting started
`composer require jaggedsoft/php-binance-api`
```php
require 'vendor/autoload.php';
$api = new Binance\API("<api key>","<secret>");
```

#### Get latest price of a symbol
```php
$ticker = $api->prices();
print_r($ticker); // List prices of all symbols
echo "Price of BNB: {$ticker['BNBBTC']} BTC";
```

### [Documentation](https://github.com/jaggedsoft/php-binance-api/wiki/1.-Getting-Started)
> The primary documentation can be found on the [wiki](https://github.com/jaggedsoft/php-binance-api/wiki).  There are also numerous other formats available.  if you would like the markdown format of the wiki, you can clone it using:  
```
git clone https://github.com/jaggedsoft/php-binance-api.wiki.git
```
