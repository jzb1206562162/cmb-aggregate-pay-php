<?php

namespace Cmb\AggregatePay\Notify;

use Cmb\AggregatePay\Crypto\CmbSmCrypto;
use Psr\Log\LoggerInterface;

class NotifyHandler
{
    private CmbSmCrypto $crypto;
    private LoggerInterface $logger;
    private $idempotentCallback;
    private $businessCallback;

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
     */
    public function serve(): void
    {
        // 兼容 JSON body 和 form-data 两种方式
        $rawInput = file_get_contents('php://input');
        $data = !empty($rawInput) ? json_decode($rawInput, true) : $_POST;

        if (!is_array($data) || empty($data)) {
            $this->logger->error('CMB Notify: empty or invalid data');
            $this->fail('Invalid request data');
            return;
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
                $this->success();
                return;
            }

            // 3. 业务处理
            if (is_callable($this->businessCallback)) {
                call_user_func($this->businessCallback, $data);
            }

            $this->success();
        } catch (\Throwable $e) {
            $this->logger->error('CMB Notify Failed: ' . $e->getMessage());
            $this->fail($e->getMessage());
        }
    }

    private function success(): void
    {
        header('Content-Type: application/json');
        echo json_encode([
            'version' => '3.4.1',
            'encoding' => 'UTF-8',
            'signMethod' => '02',
            'returnCode' => 'SUCCESS',
            'respCode' => 'SUCCESS',
            'respMsg' => 'OK'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function fail(string $msg): void
    {
        header('Content-Type: application/json', true, 200);
        echo json_encode([
            'version' => '3.4.1',
            'encoding' => 'UTF-8',
            'signMethod' => '02',
            'returnCode' => 'FAIL',
            'respCode' => 'FAIL',
            'respMsg' => $msg
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}