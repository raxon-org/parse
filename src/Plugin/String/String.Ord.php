<?php
namespace Plugin;

trait String_Ord {

    protected function string_ord(string $string): string
    {
        return ord($string);
    }

}