#!/bin/bash

composer install
./vendor/bin/phpunit --verbose --debug --bootstrap vendor/autoload.php BinanceTest
