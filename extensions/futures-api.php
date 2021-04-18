<?php
namespace Binance;

trait Futures 
{
    /**
     * 获取交易规则和交易对
     *
     * @return json containing the response
     * @throws \Exception
     */
    public function futuresExchangeInfo()
    {
        $opt = [
            "fapi" => true,
        ];

        $qstring = "fapi/v1/exchangeInfo";
        return $this->httpRequest($qstring, "GET", $opt, false);
    }
    
    /**
     * 深度信息
     *
     * @param $symbol string 交易对
     * @param $limit int 默认 500; 可选值:[5, 10, 20, 50, 100, 500, 1000]
     * @return json containing the response
     * @throws \Exception
     */
    public function futuresDepth(string $symbol, $limit = null)
    {
        $opt = [
            "fapi" => true,
            "symbol" => $symbol,
        ];

        if(!is_null($limit))
            $opt['limit'] = $limit;

        $qstring = "fapi/v1/depth";
        return $this->httpRequest($qstring, "GET", $opt, false);
    }
    
    /**
     * 近期成交
     *
     * @param $symbol string 交易对
     * @param $limit int 默认:500，最大1000
     * @return array containing the response
     * @throws \Exception
     */
    public function futuresTrades(string $symbol, $limit = null)
    {
        $opt = [
            "fapi" => true,
            "symbol" => $symbol,
        ];

        if(!is_null($limit))
            $opt['limit'] = $limit;

        $qstring = "fapi/v1/trades";
        return $this->httpRequest($qstring, "GET", $opt, false);
    }

    /**
     * 查询历史成交(MARKET_DATA)
     *
     * @param $symbol string 交易对
     * @param $limit INT 默认值:500 最大值:1000.
     * @param $fromId LONG 从哪一条成交id开始返回. 缺省返回最近的成交记录
     * @return array containing the response
     * @throws \Exception
     */
    public function futuresHistoricalTrades(string $symbol, $limit = null, $fromId = null)
    {
        $opt = [
            "fapi" => true,
            "symbol" => $symbol,
        ];

        if(!is_null($limit))
            $opt['limit'] = $limit;

        if(!is_null($fromId))
            $opt['fromId'] = $fromId;

        $qstring = "fapi/v1/historicalTrades";
        return $this->httpRequest($qstring, "GET", $opt, true);
    }

    /**
     * 近期成交(归集)
     *
     * @param $symbol string 交易对
     * @param $fromId LONG 从哪一条成交id开始返回. 缺省返回最近的成交记录
     * @param $startTime LONG 从该时刻之后的成交记录开始返回结果
     * @param $endTime LONG 返回该时刻为止的成交记录
     * @param $limit INT 默认值:500 最大值:1000.
     * @return array containing the response
     * @throws \Exception
     */
    public function futuresAggTrades(string $symbol, $fromId = null, $startTime = null, $endTime = null, $limit = null)
    {
        $opt = [
            "fapi" => true,
            "symbol" => $symbol,
        ];

        if(!is_null($limit))
            $opt['limit'] = $limit;

        if(!is_null($fromId))
            $opt['fromId'] = $fromId;

        if(!is_null($startTime))
            $opt['startTime'] = $startTime;

        if(!is_null($endTime))
            $opt['endTime'] = $endTime;

        $qstring = "fapi/v1/aggTrades";
        return $this->httpRequest($qstring, "GET", $opt, false);
    }

    /**
     * K线数据
     *
     * @param $symbol string 交易对
     * @param $interval ENUM 时间间隔
     * @param $startTime LONG 从该时刻之后的成交记录开始返回结果
     * @param $endTime LONG 返回该时刻为止的成交记录
     * @param $limit INT 默认值:500 最大值:1000.
     * @return array containing the response
     * @throws \Exception
     */
    public function futuresKlines(string $symbol, $interval, $startTime = null, $endTime = null, $limit = null)
    {
        $opt = [
            "fapi" => true,
            "symbol" => $symbol,
            "interval" => $interval,
        ];

        if(!is_null($limit))
            $opt['limit'] = $limit;

        if(!is_null($startTime))
            $opt['startTime'] = $startTime;

        if(!is_null($endTime))
            $opt['endTime'] = $endTime;

        $qstring = "fapi/v1/klines";
        return $this->httpRequest($qstring, "GET", $opt, false);
    }

    /**
     * 连续合约K线数据
     *
     * @param $pair string 标的交易对
     * @param $contractType ENUM 合约类型
     * @param $interval ENUM 时间间隔
     * @param $startTime LONG 起始时间
     * @param $endTime LONG 结束时间
     * @param $limit INT 默认值:500 最大值:1000.
     * @return array containing the response
     * @throws \Exception
     */
    public function futuresContinuousKlines(string $symbol, $contractType, $interval, $startTime = null, $endTime = null, $limit = null)
    {
        // 合约类型:
        // PERPETUAL 永续合约
        // CURRENT_MONTH 当月交割合约
        // NEXT_MONTH 次月交割合约
        // CURRENT_QUARTER 当季交割合约
        // NEXT_QUARTER 次季交割合约

        $opt = [
            "fapi" => true,
            "symbol" => $symbol,
            "contractType" => $contractType,
            "interval" => $interval,
        ];

        if(!is_null($limit))
            $opt['limit'] = $limit;

        if(!is_null($startTime))
            $opt['startTime'] = $startTime;

        if(!is_null($endTime))
            $opt['endTime'] = $endTime;

        $qstring = "fapi/v1/continuousKlines";
        return $this->httpRequest($qstring, "GET", $opt, false);
    }

    /**
     * 价格指数K线数据: 每根K线的开盘时间可视为唯一ID
     *
     * @param $pair string 标的交易对
     * @param $interval ENUM 时间间隔
     * @param $startTime LONG 从该时刻之后的成交记录开始返回结果
     * @param $endTime LONG 返回该时刻为止的成交记录
     * @param $limit INT 默认值:500 最大值:1000.
     * @return array containing the response
     * @throws \Exception
     */
    public function futuresIndexPriceKlines(string $pair, $interval, $startTime = null, $endTime = null, $limit = null)
    {
        $opt = [
            "fapi" => true,
            "pair" => $pair,
            "interval" => $interval,
        ];

        if(!is_null($limit))
            $opt['limit'] = $limit;

        if(!is_null($startTime))
            $opt['startTime'] = $startTime;

        if(!is_null($endTime))
            $opt['endTime'] = $endTime;

        $qstring = "fapi/v1/indexPriceKlines";
        return $this->httpRequest($qstring, "GET", $opt, false);
    }

    /**
     * 标记价格K线数据: 每根K线的开盘时间可视为唯一ID
     *
     * @param $symbol string 交易对
     * @param $interval ENUM 时间间隔
     * @param $startTime LONG 从该时刻之后的成交记录开始返回结果
     * @param $endTime LONG 返回该时刻为止的成交记录
     * @param $limit INT 默认值:500 最大值:1000.
     * @return array containing the response
     * @throws \Exception
     */
    public function futuresMarkPriceKlines(string $symbol, $interval, $startTime = null, $endTime = null, $limit = null)
    {
        $opt = [
            "fapi" => true,
            "symbol" => $symbol,
            "interval" => $interval,
        ];

        if(!is_null($limit))
            $opt['limit'] = $limit;

        if(!is_null($startTime))
            $opt['startTime'] = $startTime;

        if(!is_null($endTime))
            $opt['endTime'] = $endTime;

        $qstring = "fapi/v1/markPriceKlines";
        return $this->httpRequest($qstring, "GET", $opt, false);
    }

    /**
     * 最新标记价格和资金费率:采集各大交易所数据加权平均
     *
     * @param $symbol string 交易对
     * @return array|json containing the response
     * @throws \Exception
     */
    public function futuresPremiumIndex(string $symbol = null)
    {
        $opt = [
            "fapi" => true,
        ];

        if(!is_null($symbol))
            $opt['symbol'] = $symbol;

        $qstring = "fapi/v1/premiumIndex";
        return $this->httpRequest($qstring, "GET", $opt, false);
    }

    /**
     * 查询资金费率历史
     *
     * @param $symbol string 交易对
     * @param $startTime LONG 起始时间
     * @param $endTime LONG 结束时间
     * @param $limit INT 默认值:100 最大值:1000
     * @return array containing the response
     * @throws \Exception
     */
    public function futuresFundingRate(string $symbol = null, $startTime = null, $endTime = null, $limit = null)
    {
        $opt = [
            "fapi" => true,
        ];

        if(!is_null($symbol))
            $opt['symbol'] = $symbol;

        if(!is_null($limit))
            $opt['limit'] = $limit;

        if(!is_null($startTime))
            $opt['startTime'] = $startTime;

        if(!is_null($endTime))
            $opt['endTime'] = $endTime;

        $qstring = "fapi/v1/fundingRate";
        return $this->httpRequest($qstring, "GET", $opt, false);
    }

    /**
     * 24hr价格变动情况: 请注意，不携带symbol参数会返回全部交易对数据，不仅数据庞大，而且权重极高
     *
     * @param $symbol string 交易对
     * @return array|json containing the response
     * @throws \Exception
     */
    public function futuresTicker24hr(string $symbol = null)
    {
        $opt = [
            "fapi" => true,
        ];

        if(!is_null($symbol))
            $opt['symbol'] = $symbol;

        $qstring = "fapi/v1/ticker/24hr";
        return $this->httpRequest($qstring, "GET", $opt, false);
    }

    /**
     * 最新价格
     *
     * @param $symbol string 交易对
     * @return array|json containing the response
     * @throws \Exception
     */
    public function futuresTickerPrice(string $symbol = null)
    {
        $opt = [
            "fapi" => true,
        ];

        if(!is_null($symbol))
            $opt['symbol'] = $symbol;

        $qstring = "fapi/v1/ticker/price";
        return $this->httpRequest($qstring, "GET", $opt, false);
    }

    /**
     * 当前最优挂单
     *
     * @param $symbol string 交易对
     * @return array|json containing the response
     * @throws \Exception
     */
    public function futuresTickerBookTicker(string $symbol = null)
    {
        $opt = [
            "fapi" => true,
        ];

        if(!is_null($symbol))
            $opt['symbol'] = $symbol;

        $qstring = "fapi/v1/ticker/bookTicker";
        return $this->httpRequest($qstring, "GET", $opt, false);
    }

    /**
     * 获取市场强平订单: 仅可查询最近7天数据
     *
     * @param $symbol string 交易对
     * @param $startTime LONG 起始时间
     * @param $endTime LONG 结束时间,默认当前时间
     * @param $limit INT 从endTime倒推算起的数据条数，默认值:100 最大值:1000
     * @return array containing the response
     * @throws \Exception
     */
    public function futuresAllForceOrders(string $symbol = null, $startTime = null, $endTime = null, $limit = null)
    {
        $opt = [
            "fapi" => true,
        ];

        if(!is_null($symbol))
            $opt['symbol'] = $symbol;

        if(!is_null($limit))
            $opt['limit'] = $limit;

        if(!is_null($startTime))
            $opt['startTime'] = $startTime;

        if(!is_null($endTime))
            $opt['endTime'] = $endTime;

        $qstring = "fapi/v1/allForceOrders";
        return $this->httpRequest($qstring, "GET", $opt, false);
    }

    /**
     * 获取未平仓合约数
     *
     * @param $symbol string 交易对
     * @return array|json containing the response
     * @throws \Exception
     */
    public function futuresOpenInterest(string $symbol)
    {
        $opt = [
            "fapi" => true,
            "symbol" => $symbol,
        ];

        $qstring = "fapi/v1/openInterest";
        return $this->httpRequest($qstring, "GET", $opt, false);
    }

    /**
     * 合约持仓量
     *
     * @param $symbol string 交易对
     * @param $period ENUM "5m","15m","30m","1h","2h","4h","6h","12h","1d"
     * @param $limit INT default 30, max 500
     * @param $startTime LONG 起始时间
     * @param $endTime LONG 结束时间
     * @return array containing the response
     * @throws \Exception
     */
    public function futuresDataOpenInterestHist(string $symbol, $period, $limit = null, $startTime = null, $endTime = null)
    {
        $opt = [
            "fapi" => true,
            "symbol" => $symbol,
            "period" => $period,
        ];

        if(!is_null($limit))
            $opt['limit'] = $limit;

        if(!is_null($startTime))
            $opt['startTime'] = $startTime;

        if(!is_null($endTime))
            $opt['endTime'] = $endTime;

        $qstring = "futures/data/openInterestHist";
        return $this->httpRequest($qstring, "GET", $opt, false);
    }

    /**
     * 大户账户数多空比
     *
     * @param $symbol string 交易对
     * @param $period ENUM "5m","15m","30m","1h","2h","4h","6h","12h","1d"
     * @param $limit INT default 30, max 500
     * @param $startTime LONG 起始时间
     * @param $endTime LONG 结束时间
     * @return array containing the response
     * @throws \Exception
     */
    public function futuresDataTopLongShortAccountRatio(string $symbol, $period, $limit = null, $startTime = null, $endTime = null)
    {
        $opt = [
            "fapi" => true,
            "symbol" => $symbol,
            "period" => $period,
        ];

        if(!is_null($limit))
            $opt['limit'] = $limit;

        if(!is_null($startTime))
            $opt['startTime'] = $startTime;

        if(!is_null($endTime))
            $opt['endTime'] = $endTime;

        $qstring = "futures/data/topLongShortAccountRatio";
        return $this->httpRequest($qstring, "GET", $opt, false);
    }

    /**
     * 大户持仓量多空比
     *
     * @param $symbol string 交易对
     * @param $period ENUM "5m","15m","30m","1h","2h","4h","6h","12h","1d"
     * @param $limit INT default 30, max 500
     * @param $startTime LONG 起始时间
     * @param $endTime LONG 结束时间
     * @return array containing the response
     * @throws \Exception
     */
    public function futuresDataTopLongShortPositionRatio(string $symbol, $period, $limit = null, $startTime = null, $endTime = null)
    {
        $opt = [
            "fapi" => true,
            "symbol" => $symbol,
            "period" => $period,
        ];

        if(!is_null($limit))
            $opt['limit'] = $limit;

        if(!is_null($startTime))
            $opt['startTime'] = $startTime;

        if(!is_null($endTime))
            $opt['endTime'] = $endTime;

        $qstring = "futures/data/topLongShortPositionRatio";
        return $this->httpRequest($qstring, "GET", $opt, false);
    }

    /**
     * 多空持仓人数比
     *
     * @param $symbol string 交易对
     * @param $period ENUM "5m","15m","30m","1h","2h","4h","6h","12h","1d"
     * @param $limit INT default 30, max 500
     * @param $startTime LONG 起始时间
     * @param $endTime LONG 结束时间
     * @return array containing the response
     * @throws \Exception
     */
    public function futuresDataGlobalLongShortAccountRatio(string $symbol, $period, $limit = null, $startTime = null, $endTime = null)
    {
        $opt = [
            "fapi" => true,
            "symbol" => $symbol,
            "period" => $period,
        ];

        if(!is_null($limit))
            $opt['limit'] = $limit;

        if(!is_null($startTime))
            $opt['startTime'] = $startTime;

        if(!is_null($endTime))
            $opt['endTime'] = $endTime;

        $qstring = "futures/data/globalLongShortAccountRatio";
        return $this->httpRequest($qstring, "GET", $opt, false);
    }

    /**
     * 合约主动买卖量
     *
     * @param $symbol string 交易对
     * @param $period ENUM "5m","15m","30m","1h","2h","4h","6h","12h","1d"
     * @param $limit INT default 30, max 500
     * @param $startTime LONG 起始时间
     * @param $endTime LONG 结束时间
     * @return array containing the response
     * @throws \Exception
     */
    public function futuresDataTakerlongshortRatio(string $symbol, $period, $limit = null, $startTime = null, $endTime = null)
    {
        $opt = [
            "fapi" => true,
            "symbol" => $symbol,
            "period" => $period,
        ];

        if(!is_null($limit))
            $opt['limit'] = $limit;

        if(!is_null($startTime))
            $opt['startTime'] = $startTime;

        if(!is_null($endTime))
            $opt['endTime'] = $endTime;

        $qstring = "futures/data/takerlongshortRatio";
        return $this->httpRequest($qstring, "GET", $opt, false);
    }

    /**
     * 杠杆代币历史净值K线
     *
     * @param $symbol string 交易对
     * @param $interval ENUM 
     * @param $startTime LONG 起始时间
     * @param $endTime LONG 结束时间
     * @param $limit INT 默认 500, 最大 1000
     * @return array containing the response
     * @throws \Exception
     */
    public function futuresLvtKlines(string $symbol, $interval, $limit = null, $startTime = null, $endTime = null)
    {
        $opt = [
            "fapi" => true,
            "symbol" => $symbol,
            "interval" => $interval,
        ];

        if(!is_null($limit))
            $opt['limit'] = $limit;

        if(!is_null($startTime))
            $opt['startTime'] = $startTime;

        if(!is_null($endTime))
            $opt['endTime'] = $endTime;

        $qstring = "fapi/v1/lvtKlines";
        return $this->httpRequest($qstring, "GET", $opt, false);
    }

    /**
     * 综合指数交易对信息
     *
     * @param $symbol string 交易对
     * @return array containing the response
     * @throws \Exception
     */
    public function futuresIndexInfo(string $symbol = null)
    {
        $opt = [
            "fapi" => true,
        ];

        if(!is_null($symbol))
            $opt['symbol'] = $symbol;

        $qstring = "fapi/v1/indexInfo";
        return $this->httpRequest($qstring, "GET", $opt, false);
    }

    /**
     * 用户持仓风险V2 (USER_DATA)
     *
     * @param $symbol string 交易对
     * @return array containing the response
     * @throws \Exception
     */
    public function futuresPositionRisk(string $symbol)
    {
        $opt = [
            "fapi" => true,
            "symbol" => $symbol,
        ];

        $qstring = "fapi/v2/positionRisk";
        return $this->httpRequest($qstring, "GET", $opt, true);
    }
}