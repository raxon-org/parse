<?php
namespace Plugin;

trait String_Rot13 {

    protected function string_rot13(string $string): string
    {
        return str_rot13($string);
    }

}