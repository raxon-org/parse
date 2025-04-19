<?php
namespace Plugin;

trait String_Compare_Natural {

    protected function string_compare_natural(string $string1, string $string2): int
    {
        return strnatcmp($string1, $string2);
    }

}