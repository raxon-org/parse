<?php
namespace Plugin;

trait Math_Fdiv {

    protected function math_fdiv(float $x, float $y): float
    {
        return fdiv($x, $y);
    }
}