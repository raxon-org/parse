<?php
namespace Plugin;

trait String_Substring_Compare_Count_Case_Insensitive {

    protected function string_substring_count_case_insensitive(string $haystack='', string $needle='', $offset=0, $length=null): string
    {
        $haystack = strtolower($haystack);
        $needle = strtolower($needle);
        return substr_count($haystack, $needle, $offset, $length);
    }

}