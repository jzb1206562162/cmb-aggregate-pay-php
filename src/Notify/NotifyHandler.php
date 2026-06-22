<?php

namespace Cmb\AggregatePay\Notify;

use Cmb\AggregatePay\Crypto\CmbSmCrypto;
use Psr\Log\LoggerInterface;

class NotifyHandler
{
    private CmbSmCrypto $crypto;
    private LoggerInterface $logger;
    /** @var callable|null */
    private $idempotentCallback = null;
    /** @var callable|null */
    private $businessCallback = null;

    public function __construct(CmbSmCrypto $crypto, LoggerInterface $logger)
    {
        $this->crypto = $crypto;
        $this->logger = $logger;
    }

    /**
     * 幂等校验回调：function(string $orderId, string $cmbOrderId): bool
     * 返回 true = 需要处理，false = 已处理过
     */
    public function onIdempotent(callable $callback): self
    {
        $this->idempotentCallback = $callback;
        return $this;
    }

    /**
     * 业务处理回调：function(array $data): void
     */
    public function onBusiness(callable $callback): self
    {
        $this->businessCallback = $callback;
        return $this;
    }

    /**
     * 通知入口
     * 招行异步通知以 POST JSON 或 form-data 方式发送，
     * 需要 url_decode 后验签（与同步响应验签不同）
     *
     * @return string 返回给招行的 JSON 响应字符串
     */
    public function serve(): string
    {
        // 兼容 JSON body 和 form-data 两种方式
        $rawInput = file_get_contents('php://input');
        $data = !empty($rawInput) ? json_decode($rawInput, true) : $_POST;

        if (!is_array($data) || empty($data)) {
            $this->logger->error('CMB Notify: empty or invalid data');
            return $this->buildFail('Invalid request data');
        }

        $this->logger->info('CMB Notify Received', $data);

        try {
            // 1. 验签（异步通知需要 url_decode 后验签）
            if (!isset($data['sign'])) {
                throw new \Exception('Missing sign');
            }
            if (!$this->crypto->verify($data, $data['sign'])) {
                throw new \Exception('Invalid signature');
            }

            // 2. 幂等校验
            $orderId = $data['orderId'] ?? '';
            $cmbOrderId = $data['cmbOrderId'] ?? '';
            
            if (is_callable($this->idempotentCallback) && 
                !call_user_func($this->idempotentCallback, $orderId, $cmbOrderId)) {
                return $this->buildSuccess();
            }

            // 3. 业务处理
            if (is_callable($this->businessCallback)) {
                call_user_func($this->businessCallback, $data);
            }

            return $this->buildSuccess();
        } catch (\Throwable $e) {
            $this->logger->error('CMB Notify Failed: ' . $e->getMessage());
            return $this->buildFail($e->getMessage());
        }
    }

    /**
     * 构建成功响应 JSON
     */
    private function buildSuccess(): string
    {
        return json_encode([
            'version' => '3.4.1',
            'encoding' => 'UTF-8',
            'signMethod' => '02',
            'returnCode' => 'SUCCESS',
            'respCode' => 'SUCCESS',
            'respMsg' => 'OK'
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * 构建失败响应 JSON
     */
    private function buildFail(string $msg): string
    {
        return json_encode([
            'version' => '3.4.1',
            'encoding' => 'UTF-8',
            'signMethod' => '02',
            'returnCode' => 'FAIL',
            'respCode' => 'FAIL',
            'respMsg' => $msg
        ], JSON_UNESCAPED_UNICODE);
    }
}