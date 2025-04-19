<?php
namespace Plugin;

trait Math_Bin_Hex {

    protected function math_bin_hex(string $string): string
    {
        return base_convert($string, 2, 16);
    }
}