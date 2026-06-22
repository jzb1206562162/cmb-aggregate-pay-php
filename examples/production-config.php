<?php

return [
    // 基础配置
    'merId' => '你的商户号',
    'appId' => '你的APPID（需向招行单独申请API接入权限）',
    'appSecret' => '你的APP密钥（需向招行单独申请API接入权限）',

    // 国密密钥
    'privateKey' => '你的SM2私钥（Hex格式，64字符）',
    'cmbPublicKey' => '招行SM2公钥（Base64格式）',

    // 可选默认参数（配置后将自动注入到请求中，无需每次手动传）
    'userId' => 'V100001094',            // 收银员ID，招行进件时分配
    'wechatSubMchid' => '899934420',     // 微信子商户号，招行进件时分配（支付宝共用此字段，不传则默认取商管录入值）
    'notifyUrl' => 'https://your-domain.com/notify.php',  // 回调通知地址
    'termId' => '',                       // 终端号（可选）

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