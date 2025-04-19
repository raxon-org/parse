<?php
namespace Plugin;

trait String_Contains {

    protected function string_contains(string $haystack, string $needle, bool $before_needle=false): bool|string
    {
        return strstr($haystack, $needle, $before_needle);
    }

}