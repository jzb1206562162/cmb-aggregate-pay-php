<?php

namespace Cmb\AggregatePay\Traits;

use Cmb\AggregatePay\Exceptions\CmbPayException;

trait MarketingApiTrait
{
    // userId / notifyUrl / wechatSubMchid 可通过 config 自动注入

    /**
     * 4.37 微信授权码查询openid
     */
    public function queryOpenIdByAuthCode(array $params): array
    {
        $this->validateRequired($params, ['authCode']);
        return $this->request('微信授权码查询openid', $params);
    }

    /**
     * 4.38 支付宝先享后付-统一收单交易支付
     */
    public function alipayPayAfterPay(array $params): array
    {
        $this->validateRequired($params, ['orderId', 'txnAmt', 'timeoutExpress']);
        return $this->request('支付宝先享后付-统一收单交易支付', $params);
    }

    /**
     * 4.40 支付宝商户前置内容咨询
     */
    public function alipayMarketingConsult(array $params): array
    {
        $this->validateRequired($params, ['bizContent']);
        return $this->request('支付宝商户前置内容咨询', $params);
    }

    /**
     * 4.41 支付宝吱口令获取
     */
    public function alipayShareTokenCreate(array $params): array
    {
        $this->validateRequired($params, ['bizContent']);
        return $this->request('支付宝吱口令获取', $params);
    }

    /**
     * 4.24 支付宝APP支付
     */
    public function alipayAppPay(array $params): array
    {
        $this->validateRequired($params, ['orderId', 'txnAmt', 'subject', 'productCode']);
        return $this->request('支付宝APP支付', $params);
    }

    /**
     * 4.25 支付宝手机网站支付
     */
    public function alipayWapPay(array $params): array
    {
        $this->validateRequired($params, ['orderId', 'txnAmt', 'subject', 'productCode', 'quitUrl']);
        return $this->request('支付宝手机网站支付', $params);
    }

    /**
     * 4.22 微信委托代扣
     */
    public function wechatPap(array $params): array
    {
        $this->validateRequired($params, ['orderId', 'contractId', 'txnAmt']);
        return $this->request('微信委托代扣', $params);
    }

    /**
     * 4.23 微信委托代扣查询
     */
    public function queryPapOrder(array $params): array
    {
        if (!isset($params['orderId']) && !isset($params['cmbOrderId'])) {
            throw new CmbPayException('orderId 和 cmbOrderId 至少传一个');
        }
        return $this->request('微信委托代扣查询', $params);
    }

    /**
     * 微信委托代扣-支付分（pap2）
     * V3.0.0 新增，用于微信支付分场景的委托代扣
     */
    public function wechatPap2(array $params): array
    {
        $this->validateRequired($params, ['orderId', 'contractId', 'txnAmt']);
        return $this->request('微信委托代扣-支付分', $params);
    }

    /**
     * 秘钥设置（mchkey/keyset）
     * 用于设置商户通知回调加解密密钥等
     */
    public function keySet(array $params): array
    {
        $this->validateRequired($params, ['keyType']);
        return $this->request('秘钥设置', $params);
    }

    /**
     * 4.39 微信刷脸获取调用凭证
     */
    public function wechatFaceGetAuthInfo(array $params): array
    {
        $this->validateRequired($params, ['sceneInfo']);
        return $this->request('微信刷脸获取调用凭证', $params);
    }
}