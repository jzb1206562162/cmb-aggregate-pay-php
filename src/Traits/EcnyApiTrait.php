<?php

namespace Cmb\AggregatePay\Traits;

trait EcnyApiTrait
{
    private static $validEcnyTransTypes = ['TT01', 'TT03', 'TT04', 'TT13'];

    // userId / termId 可通过 config 自动注入，validateRequired 不再拦截

    /**
     * 4.17 数字人民币统一下单
     */
    public function ecnyUnifiedOrder(array $params): array
    {
        $this->validateRequired($params, [
            'orderId', 'currencyCode', 'transactionType',
            'txnAmt', 'terminalNo', 'terminalIp', 'goodsName',
            'tradePlace', 'orderTimeExpire'
        ]);
        $this->validateEnum($params['transactionType'], self::$validEcnyTransTypes, 'transactionType');
        return $this->request('数字人民币统一下单', $params);
    }

    /**
     * 4.18 数字人民币统一支付
     */
    public function ecnyUnifiedPayment(array $params): array
    {
        $this->validateRequired($params, [
            'orderId', 'currencyCode', 'transactionType',
            'txnAmt', 'terminalNo', 'terminalIp', 'goodsName',
            'tradePlace', 'orderTimeExpire', 'authCode'
        ]);
        $this->validateEnum($params['transactionType'], ['TT01'], 'transactionType');
        return $this->request('数字人民币统一支付', $params);
    }

    /**
     * 4.19 数字人民币子钱包支付
     */
    public function ecnySubWalletPay(array $params): array
    {
        $this->validateRequired($params, [
            'orderId', 'currencyCode', 'txnAmt',
            'debtorAgentId', 'authenticCode', 'authenticInfo',
            'goodsName', 'sceneId'
        ]);
        return $this->request('数字人民币子钱包支付', $params);
    }

    /**
     * 4.20 数字人民币子钱包支付-带合约
     */
    public function ecnySubWalletPayWithContract(array $params): array
    {
        $this->validateRequired($params, [
            'orderId', 'currencyCode', 'txnAmt',
            'debtorAgentId', 'authenticCode', 'authenticInfo',
            'goodsName', 'sceneId', 'contractReq'
        ]);
        return $this->request('数字人民币子钱包支付-带合约', $params);
    }

    /**
     * 4.21 数字人民币统一下单-带合约
     */
    public function ecnyUnifiedOrderWithContract(array $params): array
    {
        $this->validateRequired($params, [
            'orderId', 'currencyCode', 'transactionType',
            'txnAmt', 'terminalNo', 'terminalIp', 'goodsName',
            'tradePlace', 'orderTimeExpire', 'contractReq'
        ]);
        $this->validateEnum($params['transactionType'], self::$validEcnyTransTypes, 'transactionType');
        return $this->request('数字人民币统一下单-带合约', $params);
    }
}