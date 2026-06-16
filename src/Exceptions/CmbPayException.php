<?php

namespace Cmb\AggregatePay\Exceptions;

use RuntimeException;

class CmbPayException extends RuntimeException
{
    // 招行错误码常量
    public const ERR_SYSTEM = 'SYSTEM_ERROR';
    public const ERR_ORDER_PAID = 'ORDER_PAID';
    public const ERR_NOT_ALLOWED = 'NOT_ALLOWED';
    
    private ?string $cmbErrorCode = null;
    
    public function setCmbErrorCode(string $code): self
    {
        $this->cmbErrorCode = $code;
        return $this;
    }
    
    public function getCmbErrorCode(): ?string
    {
        return $this->cmbErrorCode;
    }
}