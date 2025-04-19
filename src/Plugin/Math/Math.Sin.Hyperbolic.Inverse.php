<?php
namespace Plugin;

trait Math_Sin_Hyperbolic_Inverse {

    protected function math_sin_hyperbolic_inverse(float $float): float
    {
        return asinh($float);
    }
}