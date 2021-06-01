<?php


namespace Binance;


use Psr\Log\AbstractLogger;

class EchoLogger extends AbstractLogger
{

    public function log($level, $message, array $context = array())
    {
        $format = "[%s][%s] - %s";
        echo sprintf($format, date("Y-m-d H:i:s"), $level, $message);
    }
}