<?php
namespace Binance;

trait Margin 
{    
    /**
     * 查詢槓桿借貸階梯
     *
     * @param  mixed $symbol
     * @return void
     */
    public function marginIsolatedLadder(string $symbol)
    {
        $opt = [
            "bapi" => true,
        ];

        $qstring = "margin/v1/friendly/isolated-margin/ladder/{$symbol}";
        return $this->httpRequest($qstring, "GET", $opt);
    }

    /**
     * 杠杆账户逐倉下单 (TRADE)
     *
     * @param $symbol string BTC
     * @param $side ENUM 订单方向 : BUY / SELL
     * @param $type ENUM 订单类型 (orderTypes, type)
     * @param $quantity DECIMAL 
     * @param $quoteOrderQty DECIMAL 
     * @param $price DECIMAL 
     * @param $stopPrice DECIMAL 与STOP_LOSS, STOP_LOSS_LIMIT, TAKE_PROFIT, 和 TAKE_PROFIT_LIMIT 订单一起使用.
     * @param $newClientOrderId string 客户自定义的唯一订单ID。若未发送自动生成。 
     * @param $icebergQty DECIMAL 与 LIMIT, STOP_LOSS_LIMIT, 和 TAKE_PROFIT_LIMIT 一起使用创建 iceberg 订单.
     * @param $newOrderRespType ENUM 设置响应: JSON. ACK, RESULT, 或 FULL; MARKET 和 LIMIT 订单类型默认为 FULL, 所有其他订单默认为 ACK.
     * @param $sideEffectType ENUM NO_SIDE_EFFECT, MARGIN_BUY, AUTO_REPAY;默认为 NO_SIDE_EFFECT.
     * @param $timeInForce ENUM GTC,IOC,FOK
     * @return array containing the response
     * @throws \Exception
     */
    public function marginIsolatedOrder(string $symbol, string $side = "BUY", string $type = "LIMIT", $quantity = null, $quoteOrderQty = null, $price = null, $stopPrice = null, $newClientOrderId = null, $icebergQty = null, $newOrderRespType = null, $sideEffectType = "NO_SIDE_EFFECT", $timeInForce = "GTC")
    {
        return $this->marginOrder($symbol, $side, $type, "TRUE", $quantity, $quoteOrderQty, $price, $stopPrice, $newClientOrderId, $icebergQty, $newOrderRespType, $sideEffectType, $timeInForce);
    }

    /**
     * 杠杆账户下单 (TRADE)
     *
     * @param $symbol string BTC
     * @param $side ENUM 订单方向 : BUY / SELL
     * @param $type ENUM 订单类型 (orderTypes, type)
     * @param $isIsolated bool 是否為逐倉交易
     * @param $quantity DECIMAL 
     * @param $quoteOrderQty DECIMAL 
     * @param $price DECIMAL 
     * @param $stopPrice DECIMAL 与STOP_LOSS, STOP_LOSS_LIMIT, TAKE_PROFIT, 和 TAKE_PROFIT_LIMIT 订单一起使用.
     * @param $newClientOrderId string 客户自定义的唯一订单ID。若未发送自动生成。 
     * @param $icebergQty DECIMAL 与 LIMIT, STOP_LOSS_LIMIT, 和 TAKE_PROFIT_LIMIT 一起使用创建 iceberg 订单.
     * @param $newOrderRespType ENUM 设置响应: JSON. ACK, RESULT, 或 FULL; MARKET 和 LIMIT 订单类型默认为 FULL, 所有其他订单默认为 ACK.
     * @param $sideEffectType ENUM NO_SIDE_EFFECT, MARGIN_BUY, AUTO_REPAY;默认为 NO_SIDE_EFFECT.
     * @param $timeInForce ENUM GTC,IOC,FOK
     * @return array containing the response
     * @throws \Exception
     */
    public function marginOrder(string $symbol, string $side = "BUY", string $type = "LIMIT", $isIsolated = "FALSE", $quantity = null, $quoteOrderQty = null, $price = null, $stopPrice = null, $newClientOrderId = null, $icebergQty = null, $newOrderRespType = null, $sideEffectType = "NO_SIDE_EFFECT", $timeInForce = "GTC")
    {
        // 类型 | 强制要求的参数
        // LIMIT | timeInForce, quantity, price
        // MARKET | quantity or quoteOrderQty
        // STOP_LOSS | quantity, stopPrice
        // STOP_LOSS_LIMIT | timeInForce, quantity, price, stopPrice
        // TAKE_PROFIT | quantity, stopPrice
        // TAKE_PROFIT_LIMIT | timeInForce, quantity, price, stopPrice
        // LIMIT_MAKER | quantity, price

        $opt = [
            "sapi" => true,
            "symbol" => $symbol,
            "side" => $side,
            "type" => $type,
            "isIsolated" => $isIsolated,
            "sideEffectType" => $sideEffectType
        ];

        switch($type) {
            case "LIMIT":
                $opt['newOrderRespType'] = 'FULL';
                $opt = array_merge($opt, compact('timeInForce', 'quantity', 'price'));
                break;
            case "MARKET":
                $opt['newOrderRespType'] = 'FULL';
                if(!is_null($quoteOrderQty))
                    $opt['quoteOrderQty'] = $quoteOrderQty;
                else
                    $opt['quantity'] = $quantity;
                break;
            case "STOP_LOSS":
                $opt['newOrderRespType'] = 'ACK';
                $opt = array_merge($opt, compact('quantity', 'stopPrice'));
                break;
            case "STOP_LOSS_LIMIT":
                $opt['newOrderRespType'] = 'ACK';
                $opt = array_merge($opt, compact('timeInForce', 'quantity', 'price', 'stopPrice'));
                break;
            case "TAKE_PROFIT":
                $opt['newOrderRespType'] = 'ACK';
                $opt = array_merge($opt, compact('quantity', 'stopPrice'));
                break;
            case "TAKE_PROFIT_LIMIT":
                $opt['newOrderRespType'] = 'ACK';
                $opt = array_merge($opt, compact('timeInForce', 'quantity', 'price', 'stopPrice'));
                break;
            case "LIMIT_MAKER":
                $opt['newOrderRespType'] = 'ACK';
                $opt = array_merge($opt, compact('quantity', 'price'));
                break;
        }

        if(!is_null($newOrderRespType))
            $opt['newOrderRespType'] = $newOrderRespType;

        $qstring = "v1/margin/order";
        return $this->httpRequest($qstring, "POST", $opt, true);
    }

    /**
     * 逐倉杠杆账户撤销订单 (TRADE)
     *
     * @param $symbol string BTC
     * @param $orderId LONG
     * @param $origClientOrderId string
     * @param $newClientOrderId string
     * @return array containing the response
     * @throws \Exception
     */
    public function marginDeleteIsolatedOrder(string $symbol, $orderId = null, string $origClientOrderId = null, string $newClientOrderId = null)
    {
        return $this->marginDeleteOrder($symbol, "TRUE", $orderId, $origClientOrderId, $newClientOrderId);
    }

    /**
     * 杠杆账户撤销订单 (TRADE)
     *
     * @param $symbol string BTC
     * @param $isIsolated bool 是否為逐倉交易
     * @param $orderId LONG 
     * @param $origClientOrderId string
     * @param $newClientOrderId string
     * @return array containing the response
     * @throws \Exception
     */
    public function marginDeleteOrder(string $symbol, $isIsolated = "FALSE", $orderId = null, string $origClientOrderId = null, string $newClientOrderId = null)
    {
        $opt = [
            "sapi" => true,
            "symbol" => $symbol,
            "isIsolated" => $isIsolated,
        ];

        if(!is_null($origClientOrderId))
            $opt['origClientOrderId'] = $origClientOrderId;
        else
            $opt['orderId'] = $orderId;

        $qstring = "v1/margin/order";
        return $this->httpRequest($qstring, "DELETE", $opt, true);
    }

    /**
     * 查询杠杆价格指数
     *
     * @param $symbol string BTC
     * @return array containing the response
     * @throws \Exception
     */
    public function marginPriceIndex(string $symbol)
    {
        $opt = [
            "sapi" => true,
            "symbol" => $symbol,
        ];

        $qstring = "v1/margin/priceIndex";
        return $this->httpRequest($qstring, "GET", $opt, true);
    }

    /**
     * 查询杠杆价格指数
     *
     * @param $symbol string BTC
     * @return array containing the response
     * @throws \Exception
     */
    public function marginPrice(string $symbol)
    {
        $ticker = $this->marginPriceIndex($symbol);
        return $ticker['price'];
    }

    /**
     * 查询逐倉杠杆账户交易历史
     *
     * @param $symbol string BTCUSDT
     * @param $startTime LONG 
     * @param $endTime LONG 
     * @param $fromId LONG 
     * @param $limit int
     * @return array containing the response
     * @throws \Exception
     */
    public function marginGetIsolatedMyTrades(string $symbol, $startTime = null, $endTime = null, $fromId = null, $limit = 500)
    {
        return $this->marginGetMyTrades($symbol, "TRUE", $startTime, $endTime, $fromId, $limit);
    }

    /**
     * 查询杠杆账户交易历史
     *
     * @param $symbol string BTCUSDT
     * @param $isIsolated bool 是否為逐倉交易
     * @param $startTime LONG 
     * @param $endTime LONG 
     * @param $fromId LONG 
     * @param $limit int
     * @return array containing the response
     * @throws \Exception
     */
    public function marginGetMyTrades(string $symbol, $isIsolated = "FALSE", $startTime = null, $endTime = null, $fromId = null, $limit = 500)
    {
        $opt = [
            "sapi" => true,
            "symbol" => $symbol,
            "isIsolated" => $isIsolated,
        ];

        if(!is_null($startTime))
            $opt['startTime'] = $startTime;

        if(!is_null($endTime))
            $opt['endTime'] = $endTime;

        if(!is_null($fromId))
            $opt['fromId'] = $fromId;

        if(!is_null($limit))
            $opt['limit'] = $limit;

        $qstring = "v1/margin/myTrades";
        return $this->httpRequest($qstring, "GET", $opt, true);
    }

    /**
     * 查询逐倉杠杆账户的所有订单
     *
     * @param $symbol string BTCUSDT
     * @param $orderId LONG 
     * @param $startTime LONG 
     * @param $endTime LONG
     * @param $limit int
     * @return array containing the response
     * @throws \Exception
     */
    public function marginGetIsolatedAllOrders(string $symbol, $orderId = null, $startTime = null, $endTime = null, $limit = 500)
    {
        return $this->marginGetAllOrders($symbol, "TRUE", $orderId, $startTime, $endTime, $limit);
    }

    /**
     * 查询杠杆账户的所有订单
     *
     * @param $symbol string BTCUSDT
     * @param $isIsolated bool 是否為逐倉交易
     * @param $orderId LONG 
     * @param $startTime LONG 
     * @param $endTime LONG
     * @param $limit int
     * @return array containing the response
     * @throws \Exception
     */
    public function marginGetAllOrders(string $symbol, $isIsolated = "FALSE", $orderId = null, $startTime = null, $endTime = null, $limit = 500)
    {
        $opt = [
            "sapi" => true,
            "symbol" => $symbol,
            "isIsolated" => $isIsolated,
        ];

        if(!is_null($startTime))
            $opt['startTime'] = $startTime;

        if(!is_null($endTime))
            $opt['endTime'] = $endTime;

        if(!is_null($orderId))
            $opt['orderId'] = $orderId;

        if(!is_null($limit))
            $opt['limit'] = $limit;

        $qstring = "v1/margin/allOrders";
        return $this->httpRequest($qstring, "GET", $opt, true);
    }

    /**
     * 逐倉杠杆账户归还借贷
     *
     * @param $asset string 
     * @param $amount int 
     * @param $symbol string BTCUSDT
     * @return array containing the response
     * @throws \Exception
     */
    public function marginIsolatedRepay($asset, $amount, string $symbol)
    {
        return $this->marginRepay($asset, $amount, "TRUE", $symbol);
    }

    /**
     * 杠杆账户归还借贷
     *
     * @param $asset string 
     * @param $amount int 
     * @param $symbol string BTCUSDT
     * @param $isIsolated bool 是否為逐倉交易
     * @return array containing the response
     * @throws \Exception
     */
    public function marginRepay($asset, $amount, $isIsolated = "FALSE", string $symbol = null)
    {
        $opt = [
            "sapi" => true,
            "asset" => $asset,
            "amount" => $amount,
            "isIsolated" => $isIsolated,
        ];

        if($isIsolated != "FALSE")
            $opt['symbol'] = $symbol;

        $qstring = "v1/margin/repay";
        return $this->httpRequest($qstring, "POST", $opt, true);
    }

    /**
     * 查询杠杆账户订单 (USER_DATA) 一些历史订单的 cummulativeQuoteQty < 0, 是指当前数据不存在。
     *
     * @param $symbol string BTC
     * @param $orderId LONG 
     * @param $origClientOrderId string
     * @return array containing the response
     * @throws \Exception
     */
    public function marginGetIsolatedOrder(string $symbol, $orderId = null, string $origClientOrderId = null)
    {
        return $this->marginGetOrder($symbol, "TRUE", $orderId, $origClientOrderId);
    }

    /**
     * 查询杠杆账户订单 (USER_DATA) 一些历史订单的 cummulativeQuoteQty < 0, 是指当前数据不存在。
     *
     * @param $symbol string BTC
     * @param $isIsolated bool 是否為逐倉交易
     * @param $orderId LONG 
     * @param $origClientOrderId string
     * @return array containing the response
     * @throws \Exception
     */
    public function marginGetOrder(string $symbol, $isIsolated = "FALSE", $orderId = null, string $origClientOrderId = null)
    {
        $opt = [
            "sapi" => true,
            "symbol" => $symbol,
            "isIsolated" => $isIsolated,
        ];

        if(!is_null($origClientOrderId))
            $opt['origClientOrderId'] = $origClientOrderId;
        else
            $opt['orderId'] = $orderId;

        $qstring = "v1/margin/order";
        return $this->httpRequest($qstring, "GET", $opt, true);
    }

    /**
     * 杠杆账户撤销单一交易对的所有逐倉挂单 (TRADE)
     *
     * @param $symbol string BTC
     * @return array containing the response
     * @throws \Exception
     */
    public function marginDeleteIsolatedOpenOrders(string $symbol)
    {
        return $this->marginDeleteOpenOrders($symbol, "TRUE");
    }

    /**
     * 杠杆账户撤销单一交易对的所有挂单 (TRADE)
     *
     * @param $symbol string BTC
     * @param $isIsolated bool 是否為逐倉交易
     * @return array containing the response
     * @throws \Exception
     */
    public function marginDeleteOpenOrders(string $symbol, $isIsolated = "FALSE")
    {
        $opt = [
            "sapi" => true,
            "symbol" => $symbol,
            "isIsolated" => $isIsolated,
        ];

        $qstring = "v1/margin/openOrders";
        return $this->httpRequest($qstring, "DELETE", $opt, true);
    }

    /**
     * 查询逐倉杠杆账户挂单记录 (USER_DATA)
     *
     * @param $symbol string BTC
     * @param $isIsolated bool 是否為逐倉交易
     * @return array containing the response
     * @throws \Exception
     */
    public function marginIsolatedOpenOrders(string $symbol)
    {
        return $this->marginOpenOrders($symbol, "TRUE");
    }

    /**
     * 查询杠杆账户挂单记录 (USER_DATA)
     *
     * @param $symbol string BTC
     * @param $isIsolated bool 是否為逐倉交易
     * @return array containing the response
     * @throws \Exception
     */
    public function marginOpenOrders(string $symbol, $isIsolated = "FALSE")
    {
        $opt = [
            "sapi" => true,
            "symbol" => $symbol,
            "isIsolated" => $isIsolated,
        ];

        $qstring = "v1/margin/openOrders";
        return $this->httpRequest($qstring, "GET", $opt, true);
    }

    /**
     * 查询杠杆逐仓账户信息
     *
     * @param $symbols string 最多可以传5个symbol; 由","分隔的字符串表示. e.g. "BTCUSDT,BNBUSDT,ADAUSDT"
     * @return array containing the response
     * @throws \Exception
     */
    public function marginIsolatedAccount(string $symbols = null)
    {
        $opt = [
            "sapi" => true,
        ];

        if(!empty($symbols))
            $opt['symbols'] = $symbols;

        $qstring = "v1/margin/isolated/account";
        return $this->httpRequest($qstring, "GET", $opt, true);
    }

    /**
     * 查询杠杆逐仓账户信息 by key
     *
     * @param $symbols string 最多可以传5个symbol; 由","分隔的字符串表示. e.g. "BTCUSDT,BNBUSDT,ADAUSDT"
     * @return array containing the response
     * @throws \Exception
     */
    public function marginIsolatedAccountByKey(string $symbols = null)
    {
        $account = $this->marginIsolatedAccount($symbols);
        if(array_key_exists('assets', $account)) {;
            $tmp = [];
            // 只記錄在 SymbolType 裡存在的資訊
            foreach($account['assets'] as $key => $value){
                $tmp[$value['symbol']] = $value;
            }
            $account['assets'] = $tmp;
        }
        return $account;
    }

    /**
     * 杠杆逐仓账户划转 (MARGIN)
     *
     * @param $asset string 被划转的资产, 比如, BTC
     * @param $symbol string 逐仓 symbol
     * @param $transFrom string "SPOT", "ISOLATED_MARGIN"
     * @param $transTo string "SPOT", "ISOLATED_MARGIN"
     * @param $amount DECIMAL 划转数量
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

    /**
     * 查询杠杆资产 (MARKET_DATA)
     *
     * @param $asset string 被划转的资产, 比如, BTC
     * @return json containing the response
     * @throws \Exception
     */
    public function marginAsset(string $asset)
    {
        $opt = [
            "sapi" => true,
            "asset" => $asset,
        ];

        $qstring = "v1/margin/asset";
        return $this->httpRequest($qstring, "GET", $opt, true);
    }

    /**
     * 获取所有杠杆资产信息 (MARKET_DATA)
     *
     * @return array containing the response
     * @throws \Exception
     */
    public function marginAllAsset()
    {
        $opt = [
            "sapi" => true,
        ];

        $qstring = "v1/margin/allAssets";
        return $this->httpRequest($qstring, "GET", $opt, true);
    }

    /**
     * 获取杠杆利率历史 (USER_DATA)
     *
     * @param $asset string 被划转的资产, 比如, BTC
     * @param $vipLevel string 默认用户当前等级
     * @param $startTime LONG 默认7天前
     * @param $endTime LONG 默认当天，时间间隔最大为3个月
     * @param $limit int 默认20，最大100
     * @return array containing the response
     * @throws \Exception
     */
    public function marginInterestRateHistory(string $asset, string $vipLevel = null, $startTime = 0, $endTime = 0, $limit = 20)
    {
        $opt = [
            "sapi" => true,
            "asset" => $asset,
        ];

        if($vipLevel)
            $opt['vipLevel'] = $vipLevel;

        if($startTime)
            $opt['startTime'] = $startTime;

        if($endTime)
            $opt['endTime'] = $endTime;

        if($limit)
            $opt['limit'] = $limit;

        $qstring = "v1/margin/interestRateHistory";
        return $this->httpRequest($qstring, "GET", $opt, true);
    }

    /**
     * 获取所有逐仓杠杆交易对
     *
     * @return array containing the response
     * @throws \Exception
     */
    public function marginIsolatedAllPairs()
    {
        $opt = [
            "sapi" => true,
        ];

        $qstring = "v1/margin/isolated/allPairs";
        return $this->httpRequest($qstring, "GET", $opt, true);
    }

    /**
     * 获取所有逐仓杠杆交易对
     *
     * @return array containing the response
     * @throws \Exception
     */
    public function marginIsolatedPairs($symbol)
    {
        $opt = [
            "sapi" => true,
            "symbol" => $symbol,
        ];

        $qstring = "v1/margin/isolated/pair";
        return $this->httpRequest($qstring, "GET", $opt, true);
    }
}