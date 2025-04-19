<?php
namespace Plugin;

trait String_Substring_Replace {

    protected function string_substring_replace(string $string='', string $replace='', int $offset=0, int $length=null): string
    {
        return substr_replace($string, $replace, $offset, $length);
    }

}