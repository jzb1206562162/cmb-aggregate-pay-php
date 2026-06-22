<?php

namespace Cmb\AggregatePay\Traits;

use Cmb\AggregatePay\Exceptions\CmbPayException;

trait OrderApiTrait
{
    // 以下参数可通过 config 自动注入，validateRequired 不再拦截：
    //   userId / wechatSubMchid / notifyUrl / termId

    /**
     * 4.1 收款码申请
     */
    public function applyQrCode(array $params): array
    {
        $this->validateRequired($params, ['orderId', 'txnAmt']);
        return $this->request('收款码申请', $params);
    }

    /**
     * 4.2 支付结果查询
     */
    public function queryOrder(array $params): array
    {
        if (!isset($params['orderId']) && !isset($params['cmbOrderId'])) {
            throw new CmbPayException('orderId 和 cmbOrderId 至少传一个');
        }
        return $this->retryRequest('支付结果查询', $params);
    }

    /**
     * 4.4 退款申请
     */
    public function refund(array $params): array
    {
        if (!isset($params['origOrderId']) && !isset($params['origCmbOrderId'])) {
            throw new CmbPayException('origOrderId 和 origCmbOrderId 至少传一个');
        }
        $this->validateRequired($params, ['orderId', 'txnAmt', 'refundAmt']);
        return $this->retryRequest('退款申请', $params);
    }

    /**
     * 4.5 退款结果查询
     */
    public function queryRefund(array $params): array
    {
        if (!isset($params['orderId']) && !isset($params['cmbOrderId'])) {
            throw new CmbPayException('orderId 和 cmbOrderId 至少传一个');
        }
        return $this->retryRequest('退款结果查询', $params);
    }

    /**
     * 4.7 关闭订单
     */
    public function closeOrder(array $params): array
    {
        if (!isset($params['origOrderId']) && !isset($params['origCmbOrderId'])) {
            throw new CmbPayException('origOrderId 和 origCmbOrderId 至少传一个');
        }
        return $this->request('关闭订单', $params);
    }

    /**
     * 4.8 付款码收款
     */
    public function payByAuthCode(array $params): array
    {
        $this->validateRequired($params, ['orderId', 'authCode', 'txnAmt']);
        return $this->request('付款码收款', $params);
    }

    /**
     * 4.9 付款码支付撤销
     */
    public function cancelPay(array $params): array
    {
        if (!isset($params['origOrderId']) && !isset($params['origCmbOrderId'])) {
            throw new CmbPayException('origOrderId 和 origCmbOrderId 至少传一个');
        }
        return $this->request('付款码支付撤销', $params);
    }

    /**
     * 4.10 微信统一下单
     */
    public function wechatUnifiedOrder(array $params): array
    {
        $this->validateRequired($params, ['orderId', 'body', 'tradeType', 'txnAmt', 'spbillCreateIp']);
        return $this->request('微信统一下单', $params);
    }

    /**
     * 4.11 服务窗支付
     */
    public function alipayServPay(array $params): array
    {
        $this->validateRequired($params, ['orderId', 'txnAmt']);
        return $this->request('服务窗支付', $params);
    }

    /**
     * 4.12 支付宝native码支付
     */
    public function alipayQrPay(array $params): array
    {
        $this->validateRequired($params, ['orderId', 'txnAmt']);
        return $this->request('支付宝native码支付', $params);
    }

    /**
     * 4.13 对账单下载地址获取
     */
    public function getStatementUrl(array $params): array
    {
        $this->validateRequired($params, ['billDate']);
        return $this->request('对账单下载地址获取', $params);
    }

    /**
     * 4.14 订单二维码申请
     */
    public function applyOrderQrCode(array $params): array
    {
        $this->validateRequired($params, ['orderId', 'txnAmt']);
        return $this->request('订单二维码申请', $params);
    }

    /**
     * 4.15 微信小程序下单
     */
    public function wechatMiniAppOrder(array $params): array
    {
        $this->validateRequired($params, ['orderId', 'body', 'tradeType', 'txnAmt', 'spbillCreateIp']);
        return $this->request('微信小程序下单', $params);
    }

    /**
     * 4.16 银联云闪付
     */
    public function unionCloudPay(array $params): array
    {
        $this->validateRequired($params, ['orderId', 'body', 'tradeScene', 'txnAmt']);
        return $this->request('银联云闪付', $params);
    }

    /**
     * 4.42 订单码关闭订单
     */
    public function closeOrderQrCode(array $params): array
    {
        if (!isset($params['origOrderId']) && !isset($params['origCmbOrderId'])) {
            throw new CmbPayException('origOrderId 和 origCmbOrderId 至少传一个');
        }
        return $this->request('订单码关闭订单', $params);
    }
}