<?php
namespace Plugin;

trait String_Position_First_Occurrence_Case_Insensitive {

    protected function string_position_first_occurrence_case_insensitive(string $haystack, string $needle, int $offset=0): bool|int
    {
        return stripos($haystack, $needle, $offset);
    }

}