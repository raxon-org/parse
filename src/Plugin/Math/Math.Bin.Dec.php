<?php
namespace Plugin;

trait Math_Bin_Dec {

    protected function math_bin_dec(string $string): string
    {
        return base_convert($string, 2, 10);
    }
}