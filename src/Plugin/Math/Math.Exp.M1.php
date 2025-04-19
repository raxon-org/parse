<?php
namespace Plugin;

trait Math_Exp_M1 {

    protected function math_exp_m1(float $float): float
    {
        return expm1($float);
    }
}