<?php
/**
 * 招商银行聚合支付 SDK 使用示例
 *
 * 提示：config 中可配置 userId/wechatSubMchid/notifyUrl/termId 作为默认值，
 * 配置后每次调用会自动注入，无需手动传参。
 * 详见 production-config.php 完整配置模板。
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Cmb\AggregatePay\CmbAggregatePay;

// ==================== 1. 初始化 ====================
$config = [
    'merId'          => 'YOUR_MERCHANT_ID',              // 商户号
    'appId'          => 'YOUR_APP_ID',                   // APP ID（需向招行单独申请）
    'appSecret'      => 'YOUR_APP_SECRET',               // APP SECRET（需向招行单独申请）
    'privateKey'     => 'YOUR_SM2_PRIVATE_KEY_HEX',      // SM2 私钥（Hex，64字符）
    'cmbPublicKey'   => 'YOUR_CMB_SM2_PUBLIC_KEY_BASE64',// 招行 SM2 公钥（Base64）
    'timeout'        => 30,                              // CURL 超时时间（秒）
    // 以下为可选默认参数，配置后自动注入到每次请求中
    'userId'         => 'V100001094',                    // 收银员ID
    'wechatSubMchid' => '899934420',                     // 微信子商户号（支付宝共用）
    'notifyUrl'      => 'https://your-domain.com/notify.php', // 回调通知地址
    'termId'         => '',                              // 终端号
];

$pay = new CmbAggregatePay($config, CmbAggregatePay::ENV_TEST);

// ==================== 2. 生成订单号 ====================
$orderId = $pay->generateOrderId();
echo "订单号: {$orderId}\n";

// ==================== 3. 收款码申请（主扫支付） ====================
// userId/notifyUrl 已配置，无需重复传
$result = $pay->applyQrCode([
    'orderId'   => $orderId,
    'txnAmt'    => $pay->yuanToFen(0.01), // 1分钱
    'body'      => '测试商品',
]);
echo "收款码: {$result['qrCode']}\n";

// ==================== 4. 支付结果查询 ====================
$result = $pay->queryOrder([
    'orderId' => $orderId,
]);
echo "订单状态: {$result['tradeState']}\n";

// ==================== 5. 退款申请 ====================
$refundOrderId = $pay->generateOrderId();
$result = $pay->refund([
    'orderId'      => $refundOrderId,
    'origOrderId'  => $orderId,
    'txnAmt'       => $pay->yuanToFen(0.01),
    'refundAmt'    => $pay->yuanToFen(0.01),
]);
echo "退款状态: {$result['refundStatus']}\n";

// ==================== 6. 微信统一下单 ====================
$result = $pay->wechatUnifiedOrder([
    'orderId'        => $pay->generateOrderId(),
    'body'           => '测试商品',
    'tradeType'      => 'JSAPI',
    'txnAmt'         => $pay->yuanToFen(0.01),
    'spbillCreateIp' => '127.0.0.1',
    'subOpenId'      => 'oUpF8u...',
]);
echo "微信预支付ID: {$result['prepayId']}\n";

// ==================== 7. 数字人民币统一下单 ====================
$result = $pay->ecnyUnifiedOrder([
    'orderId'         => $pay->generateOrderId(),
    'currencyCode'    => '156',
    'transactionType' => 'TT01',
    'txnAmt'          => $pay->yuanToFen(0.01),
    'terminalNo'      => 'TERM001',
    'terminalIp'       => '127.0.0.1',
    'goodsName'        => '测试商品',
    'tradePlace'       => '深圳南山',
    'orderTimeExpire'  => date('Y-m-d\TH:i:s', strtotime('+1 hour')),
]);
echo "数币交易码: {$result['transactionCode']}\n";

// ==================== 8. 微信支付分创建订单 ====================
$result = $pay->payscoreCreateOrder([
    'serviceId'           => '000030000000001...',
    'orderId'             => $pay->generateOrderId($config['merId'] . '0'),
    'serviceIntroduction' => '租借充电宝',
    'riskFund'            => json_encode([
        'name'   => 'ESTIMATE_ORDER_COST',
        'amount' => $pay->yuanToFen(100),
    ]),
    'timeRange' => json_encode([
        'startTime' => date('YmdHis'),
        'endTime'   => date('YmdHis', strtotime('+1 day')),
    ]),
    'wechatTradeScene' => 'WECHAT_TRADE_SCENE_CHARGING',
]);
echo "支付分包名: {$result['package']}\n";

// ==================== 9. 金额单位转换 ====================
echo "1.00元 = {$pay->yuanToFen(1.00)}分\n";
echo "100分 = {$pay->fenToYuan(100)}元\n";

// ==================== 提示 ====================
// 如果某次调用需要临时覆盖默认值，直接在 params 中传入即可：
// $pay->applyQrCode(['orderId' => 'xxx', 'notifyUrl' => 'https://another.com/notify']);
