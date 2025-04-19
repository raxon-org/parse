<?php
namespace Plugin;

trait Math_Dec_Hex {

    protected function math_dec_hex(string|int $string): string
    {
        return base_convert($string, 10, 16);
    }
}