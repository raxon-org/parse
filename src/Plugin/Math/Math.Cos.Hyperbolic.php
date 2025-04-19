<?php
namespace Plugin;

trait Math_Cos_Hyperbolic {

    protected function math_cos_hyperbolic(float $float): float
    {
        return cosh($float);
    }
}