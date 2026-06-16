<?php

namespace Cmb\AggregatePay\Traits;

use Cmb\AggregatePay\Exceptions\CmbPayException;

trait ContractApiTrait
{
    /**
     * 4.36 智能合约交易分账（新）
     */
    public function contractBenefit(array $params): array
    {
        if (!isset($params['orderId']) && !isset($params['cmbOrderId'])) {
            throw new CmbPayException('orderId 和 cmbOrderId 至少传一个');
        }
        $this->validateRequired($params, ['contractBenefitReq']);
        return $this->retryRequest('智能合约交易分账', $params);
    }

    /**
     * 4.43 智能合约交易分账结果查询
     */
    public function queryContractBenefit(array $params): array
    {
        if (!isset($params['orderId']) && !isset($params['cmbOrderId'])) {
            throw new CmbPayException('orderId 和 cmbOrderId 至少传一个');
        }
        return $this->retryRequest('智能合约交易分账结果查询', $params);
    }
}