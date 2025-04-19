<?php
namespace Plugin;

trait String_Position_First_Occurrence {

    protected function string_position_first_occurrence(string $haystack, string $needle, int $offset=0): bool|int
    {
        return strpos($haystack, $needle, $offset);
    }

}