require 'vendor/autoload.php';
$api = new Binance\API("<your key>","<your secret>");
$ticker = $api->prices();
//print_r($ticker); // List prices of all symbols
//echo "Price of BNB: {$ticker['BNBBTC']} BTC.".PHP_EOL;
$balances = $api->balances($ticker);
print_r($balances);
