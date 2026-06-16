<?php

return [
    // 基础配置
    'merId' => '你的商户号',
    'appId' => '你的APPID',
    'appSecret' => '你的APP密钥',
    
    // 国密密钥
    'privateKey' => '你的SM2私钥（Hex格式，64字符）',
    'cmbPublicKey' => '招行SM2公钥（Base64格式）',
    
    // 环境
    'env' => Cmb\AggregatePay\CmbAggregatePay::ENV_PROD,
    
    // HTTP 请求超时（秒），默认 30
    'timeout' => 30,
    
    // 重试配置
    'retry_max_attempts' => 3,
    'retry_base_delay_ms' => 200,
    
    // 日志配置
    'log_path' => __DIR__ . '/logs/cmb.log',
    'log_retention_days' => 30,
];