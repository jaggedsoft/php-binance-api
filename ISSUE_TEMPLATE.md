**Title**
- bug: XYZ broken
- feature: please add
- enhancement: add this to existing features

**Short Description:**
- Unable to get a result from blah
  
**Platform:**
- windows
- linux
- macos

**php version:**
- 7.0.24

**Long descrption**
- Doing xyz results in ypr and failing when fph

**code**
```php
require 'vendor/autoload.php';
$api = new Binance\API("<api key>","<secret>");
$ticker = $api->prices();
print_r($ticker); // List prices of all symbols
echo "Price of BNB: {$ticker['BNBBTC']} BTC";
```

**result**
```
{
   "result":"result"
}
```

thank you
