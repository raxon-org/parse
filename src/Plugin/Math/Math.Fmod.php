<?php
namespace Plugin;

trait Math_Fmod {

    protected function math_fmod(float $x, float $y): float
    {
        return fmod($x, $y);
    }
}