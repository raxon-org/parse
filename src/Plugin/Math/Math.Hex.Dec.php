<?php
namespace Plugin;

trait Math_Hex_Dec {

    protected function math_hex_dec(string $string): string
    {
        return base_convert($string, 16, 10);
    }
}