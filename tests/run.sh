#!/bin/bash

composer install
./vendor/bin/phpunit --verbose --debug --coverage-clover build/logs/clover.xml --bootstrap vendor/autoload.php BinanceTest
php ./vendor/bin/coveralls -v
