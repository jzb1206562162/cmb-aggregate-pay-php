<?php

namespace Cmb\AggregatePay\Contracts;

interface ApiInterface
{
    /**
     * 统一请求入口
     * @param string $apiName
     * @param array $params
     * @return array
     */
    public function request(string $apiName, array $params = []): array;
}