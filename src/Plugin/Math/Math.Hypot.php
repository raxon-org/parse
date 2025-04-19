<?php
namespace Plugin;

trait Math_Hypot {

    protected function math_hypot(float $x, float $y): float
    {
        return hypot($x, $y);
    }
}