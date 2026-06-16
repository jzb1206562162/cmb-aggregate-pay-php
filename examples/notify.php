<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Cmb\AggregatePay\CmbAggregatePay;
use Cmb\AggregatePay\Notify\NotifyHandler;
use Monolog\Logger;
use Monolog\Handler\RotatingFileHandler;

// 1. 生产配置
$config = [
    'merId'        => 'YOUR_MERCHANT_ID',           // 商户号
    'appId'        => 'YOUR_APP_ID',                // APP ID
    'appSecret'    => 'YOUR_APP_SECRET',            // APP SECRET
    'privateKey'   => 'YOUR_SM2_PRIVATE_KEY_HEX',   // SM2 私钥（Hex，64字符）
    'cmbPublicKey' => 'YOUR_CMB_SM2_PUBLIC_KEY_BASE64', // 招行 SM2 公钥（Base64）
    'timeout'      => 30,                           // CURL 超时时间（秒），默认 30
];

// 2. 生产日志（按天切割，保留30天）
$logger = new Logger('cmb');
$logger->pushHandler(new RotatingFileHandler(__DIR__ . '/logs/cmb_notify.log', 30));

// 3. 初始化
$pay = new CmbAggregatePay($config, CmbAggregatePay::ENV_PROD, $logger);
$handler = $pay->getNotifyHandler();

// 4. 【必须】幂等校验（示例：查数据库）
$handler->onIdempotent(function (string $orderId, string $cmbOrderId): bool {
    // 伪代码：SELECT COUNT(*) FROM orders WHERE cmb_order_id = ? AND status = 'PAID'
    // 返回 true = 未处理，false = 已处理
    return true;
});

// 5. 业务处理
$handler->onBusiness(function (array $data): void {
    // 根据 tradeState / notifyType 处理
    // 示例：更新订单状态、触发发货、分账等
    file_put_contents(
        __DIR__ . '/logs/business.log',
        date('Y-m-d H:i:s') . ' ' . json_encode($data, JSON_UNESCAPED_UNICODE) . PHP_EOL,
        FILE_APPEND
    );
});

// 6. 启动监听
$handler->serve();