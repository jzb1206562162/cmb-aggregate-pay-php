<?php

namespace Cmb\AggregatePay\Tests;

use Cmb\AggregatePay\Support\Retry;
use PHPUnit\Framework\TestCase;

class RetryTest extends TestCase
{
    public function testRetrySuccessOnFirstAttempt(): void
    {
        $callCount = 0;
        $result = Retry::execute(function () use (&$callCount) {
            $callCount++;
            return 'ok';
        }, 3);

        $this->assertEquals('ok', $result);
        $this->assertEquals(1, $callCount);
    }

    public function testRetrySuccessAfterFailure(): void
    {
        $callCount = 0;
        $result = Retry::execute(function () use (&$callCount) {
            $callCount++;
            if ($callCount < 3) {
                throw new \RuntimeException('temporary error');
            }
            return 'recovered';
        }, 3);

        $this->assertEquals('recovered', $result);
        $this->assertEquals(3, $callCount);
    }

    public function testRetryExhausted(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('persistent error');

        Retry::execute(function () {
            throw new \RuntimeException('persistent error');
        }, 3);
    }

    public function testRetryCustomDelay(): void
    {
        $start = microtime(true);
        $attempts = 0;

        try {
            Retry::execute(function () use (&$attempts) {
                $attempts++;
                throw new \RuntimeException('fail');
            }, 2, 50); // 50ms base delay
        } catch (\RuntimeException $e) {
            // expected
        }

        $elapsed = (microtime(true) - $start) * 1000;
        $this->assertEquals(2, $attempts);
        // 第一次失败后 sleep 50ms，第二次失败后结束
        $this->assertGreaterThanOrEqual(40, $elapsed, '应有至少一次延迟');
    }
}
