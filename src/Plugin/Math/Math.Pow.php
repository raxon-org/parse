<?php
namespace Plugin;

trait Math_Pow {

    protected function math_pow(int|float|null $base=null, int|float|null $exponent=null): bool|int|float
    {
        return pow($base, $exponent);
    }
}