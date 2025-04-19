<?php
namespace Plugin;

trait Math_Cos {

    protected function math_cos(float $float): float
    {
        return cos($float);
    }
}