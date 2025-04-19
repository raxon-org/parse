<?php
namespace Plugin;

trait Math_Sin_Inverse {

    protected function math_sin_inverse(float $float): float
    {
        return asin($float);
    }
}