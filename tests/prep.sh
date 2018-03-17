#!/bin/bash

mkdir -vp ~/.config/jaggedsoft/

cat > ~/.config/jaggedsoft/php-binance-api.json <<EOT
{
    "api-key": "z5RQZ9n8JcS3HLDQmPpfLQIGGQN6TTs5pCP5CTnn4nYk2ImFcew49v4ZrmP3MGl5",
    "api-secret": "ZqePF1DcLb6Oa0CfcLWH0Tva59y8qBBIqu789JEY27jq0RkOKXpNl9992By1PN9Z"
}
EOT

cd tests
mkdir -p build/logs
