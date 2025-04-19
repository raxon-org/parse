<?php
namespace Plugin;

trait String_Uppercase {

    protected function string_uppercase(string $string): string
    {
        return mb_strtoupper($string);
    }

}