<?php

namespace Cmb\AggregatePay\Support;

class IdGenerator
{
    public static function create(string $prefix = ''): string
    {
        return $prefix . date('YmdHis') . random_int(100000, 999999);
    }
}