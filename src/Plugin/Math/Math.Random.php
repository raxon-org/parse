<?php
namespace Plugin;

trait Math_Random {

    protected function math_random(int $min, int $max): int
    {
        return random_int($min, $max);
    }
}