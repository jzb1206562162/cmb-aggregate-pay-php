<?php

namespace Cmb\AggregatePay;

use Cmb\AggregatePay\Contracts\ApiInterface;
use Cmb\AggregatePay\Crypto\CmbSmCrypto;
use Cmb\AggregatePay\Notify\NotifyHandler;
use Cmb\AggregatePay\Support\IdGenerator;
use Cmb\AggregatePay\Support\Retry;
use Cmb\AggregatePay\Traits\OrderApiTrait;
use Cmb\AggregatePay\Traits\EcnyApiTrait;
use Cmb\AggregatePay\Traits\PayscoreApiTrait;
use Cmb\AggregatePay\Traits\ContractApiTrait;
use Cmb\AggregatePay\Traits\MarketingApiTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class CmbAggregatePay implements ApiInterface
{
    use OrderApiTrait, EcnyApiTrait, PayscoreApiTrait, ContractApiTrait, MarketingApiTrait;

    public const ENV_TEST = 'test';
    public const ENV_PROD = 'prod';

    private array $config;
    private string $env;
    private CmbSmCrypto $crypto;
    private LoggerInterface $logger;
    private array $apiMap;

    public function __construct(array $config, string $env = self::ENV_TEST, ?LoggerInterface $logger = null)
    {
        $this->validateConfig($config);
        $this->config = $config;
        $this->env = $env;
        $this->logger = $logger ?? new NullLogger();
        $this->crypto = new CmbSmCrypto(
            $config['privateKey'],
            $config['cmbPublicKey']
        );
        $this->initApiMap();
    }

    /**
     * 获取商户号（供 Trait 使用）
     */
    protected function getMerId(): string
    {
        return $this->config['merId'];
    }

    /**
     * 统一请求入口
     */
    public function request(string $apiName, array $params = []): array
    {
        if (!isset($this->apiMap[$apiName])) {
            throw new Exceptions\CmbPayException("API不存在: {$apiName}");
        }

        $api = $this->apiMap[$apiName];
        $url = $this->getBaseUrl() . $api['path'];
        
        // 合并公共参数
        $params = array_merge([
            'version' => '3.4.1',
            'encoding' => 'UTF-8',
            'signMethod' => '02',
            'merId' => $this->config['merId'],
        ], $params);

        // 自动注入配置中的默认参数（仅当未传入时生效）
        foreach (['userId', 'wechatSubMchid', 'notifyUrl', 'termId'] as $autoKey) {
            if (!isset($params[$autoKey]) && isset($this->config[$autoKey]) && $this->config[$autoKey] !== '') {
                $params[$autoKey] = $this->config[$autoKey];
            }
        }

        // 敏感字段加密
        $params = $this->encryptSensitiveFields($params);
        
        // 签名
        $params['sign'] = $this->crypto->sign($params);

        $this->logger->info("CMB Request: {$apiName}", ['url' => $url]);
        
        // HTTP请求
        $response = $this->httpRequest($url, $params);
        
        // 验证响应签名
        $this->verifyResponse($response);

        $result = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exceptions\CmbPayException('响应JSON解析失败');
        }

        // 处理招行系统错误
        if (isset($result['returnCode']) && $result['returnCode'] === 'FAIL') {
            $e = new Exceptions\CmbPayException($result['respMsg'] ?? '未知错误');
            $e->setCmbErrorCode($result['errCode'] ?? 'UNKNOWN');
            throw $e;
        }

        return $result;
    }

    /**
     * 获取异步通知处理器
     */
    public function getNotifyHandler(): NotifyHandler
    {
        return new NotifyHandler($this->crypto, $this->logger);
    }

    /**
     * 生成订单号（符合招行规范）
     */
    public function generateOrderId(string $prefix = ''): string
    {
        return IdGenerator::create($prefix ?: $this->config['merId']);
    }

    /**
     * 元转分
     */
    public function yuanToFen(float $yuan): int
    {
        return (int) round($yuan * 100);
    }

    /**
     * 分转元
     */
    public function fenToYuan(int $fen): float
    {
        return round($fen / 100, 2);
    }

    // ================= 私有方法 =================
    private function initApiMap(): void
    {
        $this->apiMap = [
            // 基础交易
            '收款码申请' => ['path' => 'mchorders/qrcodeapply'],
            '支付结果查询' => ['path' => 'mchorders/orderquery'],
            '退款申请' => ['path' => 'mchorders/refund'],
            '退款结果查询' => ['path' => 'mchorders/refundquery'],
            '关闭订单' => ['path' => 'mchorders/close'],
            '付款码收款' => ['path' => 'mchorders/pay'],
            '付款码支付撤销' => ['path' => 'mchorders/cancel'],
            '微信统一下单' => ['path' => 'mchorders/onlinepay'],
            '服务窗支付' => ['path' => 'mchorders/servpay'],
            '支付宝native码支付' => ['path' => 'mchorders/zfbqrcode'],
            '对账单下载地址获取' => ['path' => 'mchorders/statementurl'],
            '秘钥设置' => ['path' => 'mchkey/keyset'],
            '订单二维码申请' => ['path' => 'mchorders/orderqrcodeapply'],
            '微信小程序下单' => ['path' => 'mchorders/MiniAppOrderApply'],
            '银联云闪付' => ['path' => 'mchorders/cloudpay'],
            '订单码关闭订单' => ['path' => 'mchorders/orderqrcodeclose'],

            // 数字人民币
            '数字人民币统一下单' => ['path' => 'mchorders/ecny/unifiedOrder'],
            '数字人民币统一支付' => ['path' => 'mchorders/ecny/unifiedPayment'],
            '数字人民币子钱包支付' => ['path' => 'mchorders/ecny/subwalletpay'],
            '数字人民币子钱包支付-带合约' => ['path' => 'mchorders/ecny/contractsubwalletpay'],
            '数字人民币统一下单-带合约' => ['path' => 'mchorders/ecny/contractUnifiedOrder'],

            // 微信委托代扣
            '微信委托代扣' => ['path' => 'mchorders/pap'],
            '微信委托代扣查询' => ['path' => 'mchorders/paporderquery'],
            '微信委托代扣-支付分' => ['path' => 'mchorders/pap2'],

            // 微信支付分
            '微信支付分预授权' => ['path' => 'mchorders/payscore/permissions'],
            '微信支付分预授权查询' => ['path' => 'mchorders/payscore/querypermissions'],
            '微信支付分解除授权' => ['path' => 'mchorders/payscore/terminatepermissions'],
            '微信支付分创建订单' => ['path' => 'mchorders/payscore/order'],
            '微信支付分完结订单' => ['path' => 'mchorders/payscore/completeorder'],
            '微信支付分查询订单' => ['path' => 'mchorders/payscore/queryorder'],
            '微信支付分撤销订单' => ['path' => 'mchorders/payscore/cancelorder'],
            '微信支付分修改订单金额' => ['path' => 'mchorders/payscore/modifyorder'],

            // 智能合约
            '智能合约交易分账' => ['path' => 'mchorders/contract/benefit'],
            '智能合约交易分账结果查询' => ['path' => 'mchorders/contract/benefitquery'],

            // 营销/其他
            '微信授权码查询openid' => ['path' => 'mchorders/openidqrybyac'],
            '支付宝先享后付-统一收单交易支付' => ['path' => 'mchorders/payafteruser/pay'],
            '支付宝商户前置内容咨询' => ['path' => 'mchorders/alipay/marketing/consult'],
            '支付宝吱口令获取' => ['path' => 'mchorders/alipay/sharetoken/create'],
            '支付宝APP支付' => ['path' => 'mchorders/zfbapp'],
            '支付宝手机网站支付' => ['path' => 'mchorders/zfbwap'],
            '微信刷脸获取调用凭证' => ['path' => 'mchorders/face/getAuthInfo'],
        ];
    }

    private function getBaseUrl(): string
    {
        return $this->env === self::ENV_PROD
            ? 'https://api.cmbchina.com/polypay/v1.0/'
            : 'https://api.cmburl.cn:8065/polypay/v1.0/';
    }

    private function encryptSensitiveFields(array $params): array
    {
        $encryptKeys = [];
        foreach (['encryptIdentity', 'encryptTerminalInfo', 'encryptTradeAddressInfo'] as $field) {
            if (isset($params[$field]) && is_array($params[$field])) {
                $encrypted = $this->crypto->encryptSensitive($params[$field]);
                $params[$field] = $encrypted['value'];
                $encryptKeys[] = $encrypted['key'];
            }
        }
        // 多个敏感字段存在时，用逗号拼接 encryptKey
        if (!empty($encryptKeys)) {
            $params['encryptKey'] = implode(',', $encryptKeys);
        }
        return $params;
    }

    private function verifyResponse(string $response): void
    {
        $data = json_decode($response, true);
        if (!isset($data['sign'])) {
            throw new Exceptions\CmbPayException('响应缺少签名');
        }
        if (!$this->crypto->verify($data, $data['sign'])) {
            throw new Exceptions\CmbPayException('响应签名校验失败');
        }
    }

    private function httpRequest(string $url, array $data): string
    {
        $timestamp = time();
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data, JSON_UNESCAPED_UNICODE),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'appid: ' . $this->config['appId'],
                'timestamp: ' . $timestamp,
                'apisign: ' . $this->generateApiSign($data['sign'], $timestamp),
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => $this->env === self::ENV_PROD,
            CURLOPT_SSL_VERIFYHOST => $this->env === self::ENV_PROD ? 2 : 0,
            CURLOPT_TIMEOUT => $this->config['timeout'] ?? 30,
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($error) {
            throw new Exceptions\CmbPayException("HTTP请求失败: {$error}");
        }
        if ($httpCode != 200) {
            throw new Exceptions\CmbPayException("HTTP状态码错误: {$httpCode}");
        }
        return $response;
    }

    private function generateApiSign(string $sign, int $timestamp): string
    {
        $str = sprintf(
            'appid=%s&secret=%s&sign=%s&timestamp=%d',
            $this->config['appId'],
            $this->config['appSecret'],
            $sign,
            $timestamp
        );
        return md5($str);
    }

    /**
     * 带重试的请求（用于查询/退款等幂等接口）
     */
    protected function retryRequest(string $apiName, array $params, int $maxRetries = 3): array
    {
        return Retry::execute(fn () => $this->request($apiName, $params), $maxRetries);
    }

    protected function validateRequired(array $params, array $required): void
    {
        foreach ($required as $field) {
            if (!isset($params[$field]) || $params[$field] === '') {
                throw new \InvalidArgumentException("缺少必填参数: {$field}");
            }
        }
    }

    protected function validateEnum(string $value, array $enum, string $fieldName): void
    {
        if (!in_array($value, $enum, true)) {
            throw new \InvalidArgumentException("{$fieldName} 值无效，允许值: " . implode(',', $enum));
        }
    }

    private function validateConfig(array $config): void
    {
        $required = ['merId', 'appId', 'appSecret', 'privateKey', 'cmbPublicKey'];
        foreach ($required as $key) {
            if (!isset($config[$key]) || $config[$key] === '') {
                throw new \InvalidArgumentException("配置缺失: {$key}");
            }
        }
    }
}