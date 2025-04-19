<?php
namespace Plugin;

trait String_Compare_Case_Insensitive {

    protected function string_compare_case_insensitive(string $string1, string $string2): int
    {
        return strcasecmp($string1, $string2);
    }

}