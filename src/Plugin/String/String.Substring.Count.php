<?php
namespace Plugin;

trait String_Substring_Count {

    protected function string_substring_count(string $haystack='', string $needle='', $offset=0, $length=null): string
    {
        return substr_count($haystack, $needle, $offset, $length);
    }

}