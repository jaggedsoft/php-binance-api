#!/bin/bash

PHP_UNIT_DIR=$(pwd)
mkdir -vp ~/.config/jaggedsoft/

cat > ~/.config/jaggedsoft/php-binance-api.json <<EOT
{
    "api-key": "z5RQZ9n8JcS3HLDQmPpfLQIGGQN6TTs5pCP5CTnn4nYk2ImFcew49v4ZrmP3MGl5",
    "api-secret": "ZqePF1DcLb6Oa0CfcLWH0Tva59y8qBBIqu789JEY27jq0RkOKXpNl9992By1PN9Z"
}
EOT

cat > ./phpunit.xml <<EOT
<?xml version="1.0" encoding="UTF-8"?>
<phpunit
        backupGlobals="true"
        colors="true"
        stopOnFailure="false">

    <filter>
        <whitelist>
            <directory>$PHP_UNIT_DIR/../</directory>
            <exclude>
                <directory>$PHP_UNIT_DIR/../tests</directory>
                <directory>$PHP_UNIT_DIR/vendor</directory>
            </exclude>
        </whitelist>
    </filter>

    <testsuite name="PHP Binance Api">
        <directory suffix=".php">../</directory>
    </testsuite>

</phpunit>
EOT

mkdir -p build/logs

travis_retry composer install --no-interaction --no-suggest
wget -c -nc --retry-connrefused --tries=0 https://github.com/php-coveralls/php-coveralls/releases/download/v2.0.0/php-coveralls.phar -O coveralls.phar
chmod +x coveralls.phar
php coveralls.phar --version

sleep 5
