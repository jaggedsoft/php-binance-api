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

$tickers = $api->prices();
print_r($tickers); // List prices of all symbols
