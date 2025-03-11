<?php
namespace Plugin;


trait String_Lowercase {

    protected function string_lowercase(string $string): string
    {
        return mb_strtolower($string);
    }

}