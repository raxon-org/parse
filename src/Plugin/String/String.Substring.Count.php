<?php
namespace Plugin;

trait String_Substring_Count {

    protected function string_substring_count(string $haystack='', string $needle='', int $offset=0, int|null $length=null): string
    {
        return substr_count($haystack, $needle, $offset, $length);
    }

}