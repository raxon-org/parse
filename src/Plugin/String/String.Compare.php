<?php
namespace Plugin;

trait String_Compare {

    protected function string_compare(string $string1, string $string2): int
    {
        return strcmp($string1, $string2);
    }

}