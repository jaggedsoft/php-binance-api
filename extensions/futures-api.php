<?php
namespace Binance;

trait Futures 
{
    /**
     * 用户持仓风险V2 (USER_DATA)
     *
     * @param $symbol string BTCUSDT
     * @return array containing the response
     * @throws \Exception
     */
    public function futuresPositionRisk(string $symbol)
    {
        $opt = [
            "fapi" => true,
            "symbol" => $symbol,
        ];

        $qstring = "v2/positionRisk";
        return $this->httpRequest($qstring, "GET", $opt, true);
    }
}