<?php

namespace Cmb\AggregatePay\Tests;

use Cmb\AggregatePay\Support\IdGenerator;
use PHPUnit\Framework\TestCase;

class IdGeneratorTest extends TestCase
{
    public function testCreateWithoutPrefix(): void
    {
        $orderId = IdGenerator::create();
        $this->assertNotEmpty($orderId);
        // 格式: YmdHis + 6位随机数，至少 20 位
        $this->assertGreaterThanOrEqual(20, strlen($orderId));
    }

    public function testCreateWithPrefix(): void
    {
        $prefix = '3089991420607';
        $orderId = IdGenerator::create($prefix);
        $this->assertStringStartsWith($prefix, $orderId);
        $this->assertGreaterThan(strlen($prefix), strlen($orderId));
    }

    public function testCreateUniqueness(): void
    {
        $ids = [];
        for ($i = 0; $i < 100; $i++) {
            $ids[] = IdGenerator::create();
        }
        $this->assertCount(100, array_unique($ids), '生成的订单号应唯一');
    }
}
