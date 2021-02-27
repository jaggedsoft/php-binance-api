<?php
namespace Binance;

trait Margin 
{
    /**
     * 杠杆逐仓账户划转 (MARGIN)
     *
     * @param $asset string 被划转的资产, 比如, BTC
     * @param $symbol string 逐仓 symbol
     * @param $transFrom string "SPOT", "ISOLATED_MARGIN"
     * @param $transTo string "SPOT", "ISOLATED_MARGIN"
     * @param $amount DECIMAL is determined by the symbol bu typicall LIMIT, STOP_LOSS_LIMIT etc.
     * @return array containing the response
     * @throws \Exception
     */
    public function marginIsolatedTransfer(string $asset, string $symbol, string $transFrom, string $transTo, $amount)
    {
        $opt = [
            "sapi" => true,
            "asset" => $asset,
            "symbol" => $symbol,
            "transFrom" => $transFrom,
            "transTo" => $transTo,
            "amount" => $amount,
        ];

        // someone has preformated there 8 decimal point double already
        // dont do anything, leave them do whatever they want
        if (gettype($amount) !== "string") {
            // for every other type, lets format it appropriately
            $amount = number_format($amount, 8, '.', '');
        }

        if (is_numeric($amount) === false) {
            // WPCS: XSS OK.
            echo "warning: amount expected numeric got " . gettype($amount) . PHP_EOL;
        }

        $qstring = "v1/margin/isolated/transfer";
        return $this->httpRequest($qstring, "POST", $opt, true);
    }
}