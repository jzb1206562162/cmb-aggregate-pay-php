<?php

namespace Cmb\AggregatePay\Tests;

use Cmb\AggregatePay\CmbAggregatePay;
use PHPUnit\Framework\TestCase;

class CmbAggregatePayTest extends TestCase
{
    private array $validConfig = [
        'merId'        => '3089991420607',
        'appId'        => 'test-app-id',
        'appSecret'    => 'test-app-secret',
        'privateKey'   => 'D5F2AFA24E6BA9071B54A8C9AD735F9A1DE9C4657FA386C09B592694BC118B38',
        'cmbPublicKey' => 'MFkwEwYHKoZIzj0CAQYIKoEcz1UBgi0DQgAE6Q+fktsnY9OFP+LpSR5Udbxf5zHCFO0PmOKlFNTxDIGl8jsPbbB/9ET23NV+acSz4FEkzD74sW2iiNVHRLiKHg==',
    ];

    public function testConstructWithValidConfig(): void
    {
        $pay = new CmbAggregatePay($this->validConfig);
        $this->assertInstanceOf(CmbAggregatePay::class, $pay);
    }

    public function testConstructWithMissingConfig(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new CmbAggregatePay(['merId' => '123']);
    }

    public function testConstructWithEmptyConfigValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $config = $this->validConfig;
        $config['appId'] = '';
        new CmbAggregatePay($config);
    }

    public function testEnvDefaultToTest(): void
    {
        $pay = new CmbAggregatePay($this->validConfig);
        // 通过反射检查 env
        $ref = new \ReflectionClass($pay);
        $prop = $ref->getProperty('env');
        $prop->setAccessible(true);
        $this->assertEquals('test', $prop->getValue($pay));
    }

    public function testEnvProd(): void
    {
        $pay = new CmbAggregatePay($this->validConfig, CmbAggregatePay::ENV_PROD);
        $ref = new \ReflectionClass($pay);
        $prop = $ref->getProperty('env');
        $prop->setAccessible(true);
        $this->assertEquals('prod', $prop->getValue($pay));
    }

    public function testYuanToFen(): void
    {
        $pay = new CmbAggregatePay($this->validConfig);
        $this->assertEquals(100, $pay->yuanToFen(1.00));
        $this->assertEquals(1, $pay->yuanToFen(0.01));
        $this->assertEquals(150, $pay->yuanToFen(1.50));
        $this->assertEquals(0, $pay->yuanToFen(0.00));
    }

    public function testFenToYuan(): void
    {
        $pay = new CmbAggregatePay($this->validConfig);
        $this->assertEquals(1.00, $pay->fenToYuan(100));
        $this->assertEquals(0.01, $pay->fenToYuan(1));
        $this->assertEquals(1.50, $pay->fenToYuan(150));
        $this->assertEquals(0.00, $pay->fenToYuan(0));
    }

    public function testGenerateOrderId(): void
    {
        $pay = new CmbAggregatePay($this->validConfig);
        $orderId = $pay->generateOrderId();
        $this->assertStringStartsWith($this->validConfig['merId'], $orderId);
    }

    public function testGetNotifyHandler(): void
    {
        $pay = new CmbAggregatePay($this->validConfig);
        $handler = $pay->getNotifyHandler();
        $this->assertInstanceOf(\Cmb\AggregatePay\Notify\NotifyHandler::class, $handler);
    }

    public function testRequestUnknownApi(): void
    {
        $this->expectException(\Cmb\AggregatePay\Exceptions\CmbPayException::class);
        $this->expectExceptionMessage('API不存在');

        $pay = new CmbAggregatePay($this->validConfig);
        $pay->request('不存在的API', []);
    }
}
