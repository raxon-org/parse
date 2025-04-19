<?php
namespace Plugin;

trait Math_Oct_Dec {

    protected function math_oct_dec(string|int $string): float|int
    {
        return octdec($string);
    }
}