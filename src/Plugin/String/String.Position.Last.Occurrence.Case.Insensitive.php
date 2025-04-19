<?php
namespace Plugin;

trait String_Position_Last_Occurrence_Case_Insensitive {

    protected function string_position_last_occurrence_case_insensitive(string $haystack, string $needle, int $offset=0): bool|int
    {
        return strripos($haystack, $needle, $offset);
    }

}