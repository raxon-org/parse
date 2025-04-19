<?php
namespace Plugin;

trait Math_Pow {

    protected function math_pow(int|float $base=null, int|float $exponent=null): bool|int|float
    {
        return pow($base, $exponent);
    }
}