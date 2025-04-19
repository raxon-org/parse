<?php
namespace Plugin;

trait Math_Cos_Inverse {

    protected function math_cos_inverse(float $float): float
    {
        return acos($float);
    }
}