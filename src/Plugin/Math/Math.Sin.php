<?php
namespace Plugin;

trait Math_Sin {

    protected function math_sin(float $float): float
    {
        return sin($float);
    }
}