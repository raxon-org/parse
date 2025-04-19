<?php
namespace Plugin;

trait String_Compare_Natural_Case_Insensitive {

    protected function string_compare_natural_case_insensitive(string $string1, string $string2): int
    {
        return strnatcasecmp($string1, $string2);
    }

}