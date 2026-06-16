<?php

namespace Cmb\AggregatePay\Traits;

use Cmb\AggregatePay\Exceptions\CmbPayException;

trait PayscoreApiTrait
{
    /**
     * 4.26 微信支付分预授权
     */
    public function payscorePermission(array $params): array
    {
        $this->validateRequired($params, ['serviceId', 'authorizationCode']);
        return $this->request('微信支付分预授权', $params);
    }

    /**
     * 4.27 微信支付分预授权查询
     */
    public function payscoreQueryPermission(array $params): array
    {
        $this->validateRequired($params, ['serviceId', 'authorizationCode']);
        return $this->request('微信支付分预授权查询', $params);
    }

    /**
     * 4.28 微信支付分解除授权
     */
    public function payscoreTerminatePermission(array $params): array
    {
        $this->validateRequired($params, ['serviceId', 'authorizationCode', 'reason']);
        return $this->request('微信支付分解除授权', $params);
    }

    /**
     * 4.30 微信支付分创建订单
     */
    public function payscoreCreateOrder(array $params): array
    {
        $this->validateRequired($params, [
            'serviceId', 'orderId', 'userId', 'serviceIntroduction',
            'riskFund', 'timeRange', 'wechatTradeScene', 'notifyUrl'
        ]);
        // 招行规范：订单号必须以商户号+0开头
        $prefix = $this->config['merId'] . '0';
        if (strpos($params['orderId'], $prefix) !== 0) {
            throw new CmbPayException("支付分订单号必须以 {$prefix} 开头");
        }
        return $this->request('微信支付分创建订单', $params);
    }

    /**
     * 4.31 微信支付分完结订单
     */
    public function payscoreCompleteOrder(array $params): array
    {
        $this->validateRequired($params, ['serviceId', 'orderId', 'postPayments', 'txnAmt']);
        return $this->retryRequest('微信支付分完结订单', $params);
    }

    /**
     * 4.32 微信支付分查询订单
     */
    public function payscoreQueryOrder(array $params): array
    {
        $this->validateRequired($params, ['serviceId', 'orderId']);
        return $this->retryRequest('微信支付分查询订单', $params);
    }

    /**
     * 4.33 微信支付分撤销订单
     */
    public function payscoreCancelOrder(array $params): array
    {
        $this->validateRequired($params, ['serviceId', 'orderId', 'reason']);
        return $this->request('微信支付分撤销订单', $params);
    }

    /**
     * 4.34 微信支付分修改订单金额
     */
    public function payscoreModifyOrder(array $params): array
    {
        $this->validateRequired($params, ['serviceId', 'orderId', 'txnAmt', 'postPayments']);
        return $this->request('微信支付分修改订单金额', $params);
    }

    /**
     * 4.35 微信支付分确认订单通知（业务解析用）
     */
    public function parsePayscoreConfirmNotify(array $notifyData): array
    {
        $this->validateRequired($notifyData, ['notifyType', 'orderId', 'state']);
        if ($notifyData['notifyType'] !== 'PAYSCORE.USER_CONFIRM') {
            throw new CmbPayException('非支付分确认订单通知');
        }
        return $notifyData;
    }
}