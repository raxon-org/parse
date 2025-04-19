<?php
namespace Plugin;

trait Math_Sin_Hyperbolic {

    protected function math_sin_hyperbolic(float $float): float
    {
        return sinh($float);
    }
}