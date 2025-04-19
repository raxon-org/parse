<?php
namespace Plugin;

trait Math_Exp {

    protected function math_exp(float $float): float
    {
        return exp($float);
    }
}