<?php
namespace Plugin;

trait Math_Abs {

    protected function math_abs(float|int $number): float|int
    {
        return abs($number);
    }
}