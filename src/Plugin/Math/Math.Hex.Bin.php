<?php
namespace Plugin;

trait Math_Hex_Bin {

    protected function math_hex_bin(string $string): string
    {
        return base_convert($string, 16, 2);
    }
}