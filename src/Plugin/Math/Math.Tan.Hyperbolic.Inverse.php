<?php
namespace Plugin;

trait Math_Tan_Hyperbolic_Inverse {

    protected function math_tan_hyperbolic_inverse(float $float): float
    {
        return atanh($float);
    }
}