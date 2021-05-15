<?php
namespace Binance;

use Exception;

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
     * 更改持仓模式(TRADE)
     *
     * @param $dualSidePosition string "true": 双向持仓模式；"false": 单向持仓模式
     * @return json containing the response
     * @throws \Exception
     */
    public function futuresPositionSideDual(string $dualSidePosition = 'false')
    {
        $opt = [
            "fapi" => true,
            "dualSidePosition" => $dualSidePosition,
        ];

        $qstring = "fapi/v1/positionSide/dual";
        return $this->httpRequest($qstring, "POST", $opt, true);
    }

    /**
     * 查询持仓模式(USER_DATA)
     *
     * @param $symbol string 交易对
     * @return array containing the response
     * @throws \Exception
     */
    public function futuresGetPositionSideDual()
    {
        $opt = [
            "fapi" => true,
        ];

        $qstring = "fapi/v1/positionSide/dual";
        return $this->httpRequest($qstring, "GET", $opt, true);
    }

    /**
     * 下单 (TRADE)
     *
     * @param $symbol STRING 交易对
     * @param $side ENUM 买卖方向 SELL, BUY
     * @param $positionSide ENUM 持仓方向，单向持仓模式下非必填，默认且仅可填BOTH;在双向持仓模式下必填,且仅可选择 LONG 或 SHORT
     * @param $type ENUM 订单类型 LIMIT, MARKET, STOP, TAKE_PROFIT, STOP_MARKET, TAKE_PROFIT_MARKET, TRAILING_STOP_MARKET
     * @param $reduceOnly STRING true, false; 非双开模式下默认false；双开模式下不接受此参数； 使用closePosition不支持此参数。
     * @param $quantity DECIMAL 下单数量,使用closePosition不支持此参数。
     * @param $price DECIMAL 委托价格
     * @param $newClientOrderId STRING 用户自定义的订单号，不可以重复出现在挂单中。如空缺系统会自动赋值。必须满足正则规则 ^[\.A-Z\:/a-z0-9_-]{1,36}$
     * @param $stopPrice DECIMAL 触发价, 仅 STOP, STOP_MARKET, TAKE_PROFIT, TAKE_PROFIT_MARKET 需要此参数
     * @param $closePosition STRING	 true, false；触发后全部平仓，仅支持STOP_MARKET和TAKE_PROFIT_MARKET；不与quantity合用；自带只平仓效果，不与reduceOnly 合用
     * @param $activationPrice DECIMAL 追踪止损激活价格，仅TRAILING_STOP_MARKET 需要此参数, 默认为下单当前市场价格(支持不同workingType)
     * @param $callbackRate DECIMAL 追踪止损回调比例，可取值范围[0.1, 5],其中 1代表1% ,仅TRAILING_STOP_MARKET 需要此参数
     * @param $timeInForce ENUM 有效方法
     * @param $workingType ENUM stopPrice 触发类型: MARK_PRICE(标记价格), CONTRACT_PRICE(合约最新价). 默认 CONTRACT_PRICE
     * @param $priceProtect STRING 条件单触发保护："TRUE","FALSE", 默认"FALSE". 仅 STOP, STOP_MARKET, TAKE_PROFIT, TAKE_PROFIT_MARKET 需要此参数
     * @param $newOrderRespType ENUM "ACK", "RESULT", 默认 "ACK"
     * @param $test boolean 是否測試購買?
     * @return json containing the response
     * @throws \Exception
     */
    public function futuresOrder(string $symbol, string $side, string $type, $positionSide = null, $reduceOnly = null, $quantity = null, $price = null, $newClientOrderId = null, $stopPrice = null, $closePosition = null, $activationPrice = null, $callbackRate = null, $timeInForce = null, $workingType = null, $priceProtect = null, $newOrderRespType = "RESULT", bool $test = false)
    {
        $opt = [
            "fapi" => true,
            "symbol" => $symbol,
            "side" => $side,
            "type" => $type,
        ];

        if(!is_null($newOrderRespType))
            $opt['newOrderRespType'] = $newOrderRespType;

        if(!is_null($closePosition) and $closePosition != 'false') {
            if(is_null($stopPrice))
                throw new Exception('當 closePosition 為 true 強制要求參數：stopPrice');
            $opt['stopPrice'] = $stopPrice;
            $opt['closePosition'] = $closePosition;
        }
        else {
            // 驗證必要參數
            switch($type) 
            {
                case 'LIMIT':
                    if(is_null($timeInForce) or is_null($quantity) or is_null($price))
                        throw new Exception('根據 order type的不同，強制要求參數：timeInForce, quantity, price');
                    $opt['timeInForce'] = $timeInForce;
                    $opt['quantity'] = $quantity;
                    $opt['price'] = $price;
                    break;
                case 'MARKET':
                    if(is_null($quantity))
                        throw new Exception('根據 order type的不同，強制要求參數：quantity');
                    $opt['quantity'] = $quantity;
                    break;
                case 'STOP':
                case 'TAKE_PROFIT':
                    if(is_null($quantity) or is_null($price) or is_null($stopPrice))
                        throw new Exception('根據 order type的不同，強制要求參數：quantity, price, stopPrice');
                    $opt['quantity'] = $quantity;
                    $opt['price'] = $price;
                    $opt['stopPrice'] = $stopPrice;
                    break;
                case 'STOP_MARKET':
                case 'TAKE_PROFIT_MARKET':
                    if(is_null($stopPrice))
                        throw new Exception('根據 order type的不同，強制要求參數：stopPrice');
                    $opt['stopPrice'] = $stopPrice;
                    break;
                case 'TRAILING_STOP_MARKET':
                    if(is_null($callbackRate))
                        throw new Exception('根據 order type的不同，強制要求參數：callbackRate');
                    $opt['callbackRate'] = $callbackRate;
                    break;
            }
        }

        if(!is_null($positionSide))
            $opt['positionSide'] = $positionSide;

        if(!is_null($reduceOnly))
            $opt['reduceOnly'] = $reduceOnly;

        if(!is_null($newClientOrderId))
            $opt['newClientOrderId'] = $newClientOrderId;

        if(!is_null($activationPrice))
            $opt['activationPrice'] = $activationPrice;

        if(!is_null($workingType))
            $opt['workingType'] = $workingType;

        if(!is_null($priceProtect))
            $opt['priceProtect'] = $priceProtect;

        if(!is_null($newOrderRespType))
            $opt['newOrderRespType'] = $newOrderRespType;

        $qstring = ($test) ? "fapi/v1/order/test" : "fapi/v1/order";
        return $this->httpRequest($qstring, "POST", $opt, true);
    }

    /**
     * 触发后全部平仓，
     *
     * @param $symbol STRING 交易对
     * @param $side ENUM 买卖方向 SELL, BUY
     * @param $type ENUM 订单类型 LIMIT, MARKET, STOP, TAKE_PROFIT, STOP_MARKET, TAKE_PROFIT_MARKET, TRAILING_STOP_MARKET
     * @param $stop_price int 限價
     * @throws \Exception
     */
    public function featuresClosePositionOrder(string $symbol, string $side, string $type = 'STOP_MARKET', $stop_price, bool $test = false)
    {
        return $this->futuresOrder($symbol, $side, $type, null, null, null, null, null, $stop_price, 'true', null, null, null, null, null, "RESULT", $test);
    }

    /**
     * 查询订单 (USER_DATA)
     *
     * @param $symbol string 交易对
     * @param $orderId LONG 系统订单号
     * @param $origClientOrderId string 用户自定义的订单号
     * @return array containing the response
     * @throws \Exception
     */
    public function futuresGetOrder(string $symbol, $orderId = null, $origClientOrderId = null)
    {
        $opt = [
            "fapi" => true,
            "symbol" => $symbol,
        ];
        
        if(!is_null($orderId))
            $opt['orderId'] = $orderId;

        if(!is_null($origClientOrderId))
            $opt['origClientOrderId'] = $origClientOrderId;

        $qstring = "fapi/v1/order";
        return $this->httpRequest($qstring, "GET", $opt, true);
    }

    /**
     * 查询订单 (USER_DATA)
     *
     * @param $symbol string 交易对
     * @param $orderId LONG 只返回此orderID及之后的订单，缺省返回最近的订单
     * @param $startTime string 起始时间
     * @param $endTime string 结束时间
     * @param $limit int 返回的结果集数量 默认值:500 最大值:1000
     * @return array containing the response
     * @throws \Exception
     */
    public function futuresAllOrders(string $symbol, $orderId = null, $startTime = null, $endTime = null, $limit = null)
    {
        $opt = [
            "fapi" => true,
            "symbol" => $symbol,
        ];
        
        if(!is_null($orderId))
            $opt['orderId'] = $orderId;

        if(!is_null($startTime))
            $opt['startTime'] = $startTime;

        if(!is_null($endTime))
            $opt['endTime'] = $endTime;

        if(!is_null($limit))
            $opt['limit'] = $limit;

        $qstring = "fapi/v1/allOrders";
        return $this->httpRequest($qstring, "GET", $opt, true);
    }

    /**
     * 查询订单 (USER_DATA)
     *
     * @param $symbol string 交易对
     * @param $orderId LONG 系统订单号
     * @param $origClientOrderId string 用户自定义的订单号
     * @return array containing the response
     * @throws \Exception
     */
    public function futuresDeleteOrder(string $symbol, $orderId = null, $origClientOrderId = null)
    {
        $opt = [
            "fapi" => true,
            "symbol" => $symbol,
        ];
        
        if(!is_null($orderId))
            $opt['orderId'] = $orderId;

        if(!is_null($origClientOrderId))
            $opt['origClientOrderId'] = $origClientOrderId;

        $qstring = "fapi/v1/order";
        return $this->httpRequest($qstring, "DELETE", $opt, true);
    }

    /**
     * 撤销全部订单 (TRADE)
     *
     * @param $symbol string 交易对
     * @return array containing the response
     * @throws \Exception
     */
    public function futuresDeleteAllOpenOrders(string $symbol)
    {
        $opt = [
            "fapi" => true,
            "symbol" => $symbol,
        ];
        
        $qstring = "fapi/v1/allOpenOrders";
        return $this->httpRequest($qstring, "DELETE", $opt, true);
    }

    /**
     * 倒计时撤销所有订单 (TRADE)
     *
     * @param $symbol string 交易对
     * @param $countdownTime LONG 倒计时。 1000 表示 1 秒； 0 表示取消倒计时撤单功能。
     * @return array containing the response
     * @throws \Exception
     */
    public function futuresCountdownCancelAll(string $symbol, $countdownTime = 1000)
    {
        $opt = [
            "fapi" => true,
            "symbol" => $symbol,
            "countdownTime" => $countdownTime,
        ];
        
        $qstring = "fapi/v1/countdownCancelAll";
        return $this->httpRequest($qstring, "POST", $opt, true);
    }

    /**
     * 查询当前挂单 (USER_DATA)
     *
     * @param $symbol string 交易对
     * @param $orderId LONG 系统订单号
     * @param $origClientOrderId string 用户自定义的订单号
     * @return array containing the response
     * @throws \Exception
     */
    public function futuresOpenOrder(string $symbol, $orderId = null, $origClientOrderId = null)
    {
        $opt = [
            "fapi" => true,
            "symbol" => $symbol,
        ];
        
        if(!is_null($orderId))
            $opt['orderId'] = $orderId;

        if(!is_null($origClientOrderId))
            $opt['origClientOrderId'] = $origClientOrderId;
        
        $qstring = "fapi/v1/openOrder";
        return $this->httpRequest($qstring, "GET", $opt, true);
    }

    /**
     * 查看当前全部挂单 (USER_DATA)
     *
     * @return array containing the response
     * @throws \Exception
     */
    public function futuresOpenOrders(string $symbol = null)
    {
        $opt = [
            "fapi" => true,
        ];

        if(!is_null($symbol))
            $opt['symbol'] = $symbol;
        
        $qstring = "fapi/v1/openOrders";
        return $this->httpRequest($qstring, "GET", $opt, true);
    }

    /**
     * 账户信息V2 by key
     *
     * @param $symbols string 最多可以传5个symbol; 由","分隔的字符串表示. e.g. "BTCUSDT,BNBUSDT,ADAUSDT"
     * @return array containing the response
     * @throws \Exception
     */
    public function futuresAccountByKey()
    {
        $account = $this->futuresAccount();
        if(array_key_exists('assets', $account)) {;
            $tmp = [];
            // 只記錄在 SymbolType 裡存在的資訊
            foreach($account['assets'] as $key => $value){
                $tmp[$value['asset']] = $value;
            }
            $account['assets'] = $tmp;
        }
        if(array_key_exists('positions', $account)) {;
            $tmp = [];
            // 只記錄在 SymbolType 裡存在的資訊
            foreach($account['positions'] as $key => $value){
                $tmp[$value['symbol']] = $value;
            }
            $account['positions'] = $tmp;
        }
        return $account;
    }

    /**
     * 账户信息V2 (USER_DATA)
     *
     * @return array containing the response
     * @throws \Exception
     */
    public function futuresAccount()
    {
        $opt = [
            "fapi" => true,
        ];

        $qstring = "fapi/v2/account";
        return $this->httpRequest($qstring, "GET", $opt, true);
    }

    /**
     * 调整开仓杠杆 (TRADE)
     *
     * @param $symbol string 交易对
     * @param $leverage int 目标杠杆倍数：1 到 125 整数
     * @return array containing the response
     * @throws \Exception
     */
    public function futuresLeverage(string $symbol, $leverage = 5)
    {
        $opt = [
            "fapi" => true,
            "symbol" => $symbol,
            "leverage" => $leverage,
        ];
        
        $qstring = "fapi/v1/leverage";
        return $this->httpRequest($qstring, "POST", $opt, true);
    }

    /**
     * 变换逐仓模式 (TRADE)
     *
     * @param $symbol string 交易对
     * @param $marginType ENUM 保证金模式 ISOLATED(逐仓), CROSSED(全仓)
     * @return array containing the response
     * @throws \Exception
     */
    public function futuresIsolatedMarginType(string $symbol)
    {
        return $this->futuresMarginType($symbol, 'ISOLATED');
    }

    /**
     * 变换逐全仓模式 (TRADE)
     *
     * @param $symbol string 交易对
     * @param $marginType ENUM 保证金模式 ISOLATED(逐仓), CROSSED(全仓)
     * @return array containing the response
     * @throws \Exception
     */
    public function futuresMarginType(string $symbol, $marginType = 'CROSSED')
    {
        $opt = [
            "fapi" => true,
            "symbol" => $symbol,
            "marginType" => $marginType,
        ];
        
        $qstring = "fapi/v1/marginType";
        return $this->httpRequest($qstring, "POST", $opt, true);
    }

    /**
     * 调整逐仓保证金 (TRADE)
     *
     * @param $symbol string 交易对
     * @param $positionSide ENUM 持仓方向，单向持仓模式下非必填，默认且仅可填BOTH;在双向持仓模式下必填,且仅可选择 LONG 或 SHORT
     * @param $amount DECIMAL 保证金资金
     * @param $type int 调整方向 1: 增加逐仓保证金，2: 减少逐仓保证金
     * @return array containing the response
     * @throws \Exception
     */
    public function futuresPositionMargin(string $symbol, $amount, $type = 1, $positionSide = null)
    {
        $opt = [
            "fapi" => true,
            "symbol" => $symbol,
            "amount" => $amount,
            "type" => $type,
        ];

        if(!is_null($positionSide))
            $opt['positionSide'] = $positionSide;
        
        $qstring = "fapi/v1/positionMargin";
        return $this->httpRequest($qstring, "POST", $opt, true);
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

    /**
     * 获取账户损益资金流水(USER_DATA)
     *
     * @param $symbol string 交易对
     * @param $incomeType STRING 收益类型 "TRANSFER"，"WELCOME_BONUS", "REALIZED_PNL"，"FUNDING_FEE", "COMMISSION", and "INSURANCE_CLEAR"
     * @param $startTime LONG 起始时间
     * @param $endTime LONG 结束时间
     * @param $limit INT 返回的结果集数量 默认值:100 最大值:1000
     * @return array containing the response
     * @throws \Exception
     */
    public function futuresIncome(string $symbol = null, $incomeType = null, $startTime = null, $endTime = null, $limit = null)
    {
        $opt = [
            "fapi" => true,
        ];

        if(!is_null($symbol))
            $opt['symbol'] = $symbol;

        if(!is_null($incomeType))
            $opt['incomeType'] = $incomeType;

        if(!is_null($startTime))
            $opt['startTime'] = $startTime;

        if(!is_null($endTime))
            $opt['endTime'] = $endTime;

        if(!is_null($limit))
            $opt['limit'] = $limit;

        $qstring = "fapi/v1/income";
        return $this->httpRequest($qstring, "GET", $opt, true);
    }

    /**
     * 用户手续费率 (USER_DATA)
     *
     * @param $symbol string 交易对
     * @return array containing the response
     * @throws \Exception
     */
    public function futuresCommissionRate(string $symbol)
    {
        $opt = [
            "fapi" => true,
            "symbol" => $symbol,
        ];

        $qstring = "fapi/v1/commissionRate";
        return $this->httpRequest($qstring, "GET", $opt, true);
        // return: {
        //     "symbol": "BTCUSDT",
        //     "makerCommissionRate": "0.0002",  // 0.02%
        //     "takerCommissionRate": "0.0004"   // 0.04%
        // }
    }
}