<?php
namespace Plugin;

trait Math_Tan_Inverse {

    protected function math_tan_inverse(float $float): float
    {
        return atan($float);
    }
}