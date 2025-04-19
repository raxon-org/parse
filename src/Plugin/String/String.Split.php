<?php
namespace Plugin;

trait String_Split {

    protected function string_split(string $string, int $size=1, string $encoding=null): string
    {
        return mb_str_split($string, $size, $encoding);
    }

}