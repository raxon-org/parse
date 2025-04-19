<?php
namespace Plugin;

trait Math_Floor {

    protected function math_floor(float|int $float): float
    {
        return floor($float);
    }
}