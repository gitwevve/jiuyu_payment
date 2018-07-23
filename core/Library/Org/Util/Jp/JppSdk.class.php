<?php
namespace Org\Util\Jp;
/**
 * 九派天下商户支付SDK
 *12345633320170906154042
 * @author: hongcq
 * @since 2017-08-02
 */
class JppSdk
{
    // ------------------------- 卡相关接口 -------------------------------- //

    /**
     * 快捷绑卡
     *
     * @param array $params
     * @return array
     */
    public static function bindCard($params)
    {
        return service\CardService::bind($params);
    }

    /**
     * 解绑卡
     *
     * @param array $params
     * @return array
     */
    public static function unbindCard($params)
    {
        return service\CardService::unbind($params);
    }

    /**
     * 快捷支付短信下发／重发
     *
     * @param array $params
     * @return array
     */
    public static function smsSend($params)
    {
        return service\SmsService::sendText($params);
    }

    /**
     * 查询用户的绑卡信息
     *
     * @param array $params
     * @return array
     */
    public static function userCardList($params)
    {
        return service\CardService::userCardList($params);
    }

    /**
     * 商户查询银行卡的签约状态
     *
     * @param array $params
     * @return array
     */
    public static function bindStatus($params)
    {
        return service\CardService::bindStatus($params);
    }

    /**
     * 查询银行卡信息
     *
     * @param array $params
     * @return array
     */
    public static function cardDetail($params)
    {
        return service\CardService::cardDetail($params);
    }

    /**
     * 查询支持绑卡的银行列表
     *
     * @param array $params
     * @return array
     */
    public static function supportBindList($params)
    {
        return service\CardService::supportBindList($params);
    }

    // ------------------------- 支付相关接口 -------------------------------- //

    /**
     * 快捷支付发起(九派支付方验证短信)
     *
     * @param array $params
     * @return array
     */
    public static function quickPayInit($params)
    {
        return service\PayService::quickPayInit($params);
    }

    /**
     * 快捷支付提交(九派支付方验证短信)
     *
     * @param array $params
     * @return array
     */
    public static function quickPayCommit($params)
    {
        return service\PayService::quickPayCommit($params);
    }

    /**
     * 快捷支付提交(商户自验短信)
     *
     * @param array $params
     * @return array
     */
    public static function quickPay($params)
    {
        return service\PayService::quickPay($params);
    }

    /**
     * 快捷支付查询
     *
     * @param array $params
     * @return array
     */
    public static function payQuery($params)
    {
        return service\PayService::payQuery($params);
    }

    /**
     * 退款
     *
     * @param array $params
     * @return array
     */
    public static function refund($params)
    {
        return service\PayService::refund($params);
    }

    /**
     * 退款结果查询
     *
     * @param array $params
     * @return array
     */
    public static function refundQuery($params)
    {
        return service\PayService::refundQuery($params);
    }

    /**
     * 网银支付
     *
     * @param array $params
     * @return array
     */
    public static function bankPayment($params)
    {
        return service\PayService::bankPayment($params);
    }

    // ------------------------- 账单相关接口 -------------------------------- //

    /**
     * 对账单查询下载
     *
     * @param array $params
     * @return array
     */
    public static function statementDailyQuery($params)
    {
        return service\StatementService::statementDailyQuery($params);
    }

    // ------------------------- 代收相关接口 -------------------------------- //

    /**
     * 商户调用该交易接口扣客户账(单笔)
     *
     * @param array $params
     * @return array
     */
    public static function singleCollection($params)
    {
        return service\CollectionService::singleCollection($params);
    }

    /**
     * 商户调用该交易接口扣客户账(批量多笔)
     *
     * @param array $params
     * @return array
     */
    public static function batchCollection($params)
    {
        return service\CollectionService::batchCollection($params);
    }

    /**
     * 批量多笔代收查询
     *
     * @param array $params
     * @return array
     */
    public static function batchCollectionQuery($params)
    {
        return service\CollectionService::batchCollectionQuery($params);
    }

    /**
     * 单笔代付
     *
     * @param array $params
     * @return array
     */
    public static function singleTransfer($params)
    {
        return service\CollectionService::singleTransfer($params);
    }

    public static function capOrderQuery($params){
        return service\CollectionService::orderQuery($params);
    }

    /**
     * 批量多笔代付
     *
     * @param array $params
     * @return array
     */
    public static function batchTransfer($params)
    {
        return service\CollectionService::batchTransfer($params);
    }

    /**
     * 查询账户余额
     *
     * @param array $params
     * @return array
     */
    public static function merchantAccountQuery($params)
    {
        return service\CollectionService::merchantAccountQuery($params);
    }

    // ------------------------- 对账单相关接口 -------------------------------- //

    /**
     * 定单查询下载
     *
     * @param array $params
     * @return array
     */
    public static function orderQuery($params)
    {
        return service\CollectionService::orderQuery($params);
    }

    /**
     * 对账单查询下载
     *
     * @param array $params
     * @return array
     */
    public static function statementQuery($params)
    {
        return service\StatementService::statementQuery($params);
    }

    /**
     * 对账单下载
     *
     * @param array $params
     * @return array
     */
    public static function statementDownload($params)
    {
        return service\StatementService::statementDownload($params);
    }

    /**
     * 对账单查询
     *
     * @param array $params
     * @return array
     */
    public static function capStatementQuery($params)
    {
        return service\StatementService::capStatementQuery($params);
    }

    // ------------------------- 扫码支付相关接口 -------------------------------- //

    /**
     * 生成固码
     *
     * @param array $params
     * @return array
     */
    public static function scanCreateSolidCode($params = array())
    {
        return service\ScanService::createSolidCode($params);
    }

    /**
     * 生成订单活码
     *
     * @param array $params
     * @return array
     */
    public static function scanCreateOrderCode($params)
    {
        return service\ScanService::createOrderCode($params);
    }

    /**
     * 条码支付确认
     *
     * @param array $params
     * @return array
     */
    public static function scanPayConfirmViaCodeBar($params)
    {
        return service\ScanService::payConfirmViaCodeBar($params);
    }

    /**
     * 活码/固码支付确认
     *
     * @param array $params
     * @return array
     */
    public static function scanPayConfirm($params)
    {
        return service\ScanService::payConfirm($params);
    }

    /**
     * 扫码订单撤销
     *
     * @param array $params
     * @return array
     */
    public static function scanPayCancel($params)
    {
        return service\ScanService::payCancel($params);
    }

    // ------------------------- B2B B2C 支付相关接口 -------------------------------- //

}
