<?php

require '../php-binance-api.php';

/*
mkdir -vp ~/.config/jaggedsoft/
cat >  ~/.config/jaggedsoft/php-binance-api.json << EOF
{
    "api-key": "<api key>",
    "api-secret": "<secret>"
}
*/

$api = new Binance\API();

$ticker = $api->prices();
print_r($ticker); // List prices of all symbols
echo "Price of BNB: {$ticker['BNBBTC']} BTC.".PHP_EOL;
