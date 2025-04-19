<?php
namespace Plugin;

trait String_Levensthein {

    protected function string_length(string $string1, string $string2, int $cost_insert=1, int $cost_replace=1, int $cost_delete=1): string
    {
        return levenshtein($string1, $string2, $cost_insert, $cost_replace, $cost_delete);
    }

}