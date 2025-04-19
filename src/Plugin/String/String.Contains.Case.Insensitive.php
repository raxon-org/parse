<?php
namespace Plugin;

trait String_Contains_Case_Insensitive {

    protected function string_contains_case_insensitive(string $haystack, string $needle, bool $before_needle=false): bool|string
    {
        return stristr($haystack, $needle, $before_needle);
    }

}