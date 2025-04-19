<?php
namespace Plugin;

trait Math_Sqrt {

    protected function math_sin(float $float): float
    {
        return sqrt($float);
    }
}