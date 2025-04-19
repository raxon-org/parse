<?php
namespace Plugin;

trait String_Position_Last_Occurrence {

    protected function string_position_last_occurrence(string $haystack, string $needle, int $offset=0): bool|int
    {
        return strrpos($haystack, $needle, $offset);
    }

}