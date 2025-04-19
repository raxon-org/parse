<?php
namespace Plugin;

trait String_Length {

    protected function string_length(string $string): string
    {
        return strlen($string);
    }

}