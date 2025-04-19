<?php
namespace Plugin;

trait String_Reverse {

    protected function string_reverse(string $string): string
    {
        return strrev($string);
    }

}