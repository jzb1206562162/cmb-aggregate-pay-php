<?php

namespace Cmb\AggregatePay\Support;

class IdGenerator
{
    public static function create(string $prefix = ''): string
    {
        return $prefix . date('YmdHis') . mt_rand(100000, 999999);
    }
}