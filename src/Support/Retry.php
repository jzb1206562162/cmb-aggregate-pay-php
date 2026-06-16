<?php

namespace Cmb\AggregatePay\Support;

class Retry
{
    /**
     * 指数退避重试
     */
    public static function execute(callable $callback, int $maxAttempts = 3, int $baseDelayMs = 200): mixed
    {
        $attempt = 0;
        while ($attempt < $maxAttempts) {
            try {
                return $callback();
            } catch (\Throwable $e) {
                $attempt++;
                if ($attempt >= $maxAttempts) {
                    throw $e;
                }
                // 200ms, 400ms, 800ms...
                usleep($baseDelayMs * 1000 * (2 ** ($attempt - 1)));
            }
        }
        return null;
    }
}