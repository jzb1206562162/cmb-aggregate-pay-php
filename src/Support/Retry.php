<?php

namespace Cmb\AggregatePay\Support;

class Retry
{
    /**
     * 指数退避重试
     *
     * @param callable $callback     回调函数
     * @param int      $maxAttempts  最大尝试次数
     * @param int      $baseDelayMs  基础延迟（毫秒），延迟 = baseDelayMs * 2^(attempt-1)
     * @return mixed                 回调返回值
     * @throws \Throwable            最后一次尝试的异常
     */
    public static function execute(callable $callback, int $maxAttempts = 3, int $baseDelayMs = 200)
    {
        $attempt = 0;
        $lastException = null;

        while ($attempt < $maxAttempts) {
            try {
                return $callback();
            } catch (\Throwable $e) {
                $lastException = $e;
                $attempt++;
                if ($attempt >= $maxAttempts) {
                    break;
                }
                // 指数退避：200ms, 400ms, 800ms...
                usleep($baseDelayMs * 1000 * (2 ** ($attempt - 1)));
            }
        }

        throw $lastException;
    }
}