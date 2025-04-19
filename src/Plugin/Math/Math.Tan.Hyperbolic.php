<?php
namespace Plugin;

trait Math_Tan_Hyperbolic {

    protected function math_tan_hyperbolic(float $float): float
    {
        return tanh($float);
    }
}