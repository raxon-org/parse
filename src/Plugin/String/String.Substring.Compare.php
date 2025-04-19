<?php
namespace Plugin;

trait String_Substring_Compare {

    protected function string_substring_compare(string  $string1='', string $string2='', int $offset=0, int $length=null, bool $case_insensitive=false): string
    {
        return substr_compare($string1, $string2, $offset, $length, $case_insensitive);
    }

}