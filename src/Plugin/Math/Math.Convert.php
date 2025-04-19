<?php
namespace Plugin;

trait Math_Convert {

    protected function math_convert(string $string='', int $from=10, int $to=10): float
    {
        return base_convert($string, $from, $to);
    }
}