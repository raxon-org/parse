<?php
namespace Plugin;

trait Math_Dec_Bin {

    protected function math_dec_bin(string|int $string): string
    {
        return base_convert($string, 10, 2);
    }
}