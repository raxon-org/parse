<?php
namespace Plugin;


trait String_Uppercase_First {

    protected function string_uppercase_first(string $string): string
    {
        return ucfirst($string);
    }

}