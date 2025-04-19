<?php
namespace Plugin;

trait Math_Tan_Inverse_2 {

    protected function math_tan_inverse_2(float $y, float $x): float
    {
        return atan2($y, $x);
    }
}