<?php
namespace Plugin;

trait String_Word_Wrap {

    protected function string_value(string $string, int $width=75, string $break="\n", bool $cut=false): string
    {
        return wordwrap($string, $width, $break, $cut);
    }

}