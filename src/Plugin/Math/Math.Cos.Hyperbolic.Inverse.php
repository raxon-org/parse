<?php
namespace Plugin;

trait Math_Cos_Hyperbolic_Inverse {

    protected function math_cos_hyperbolic_inverse(float $float): float
    {
        return acosh($float);
    }
}