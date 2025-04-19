<?php
namespace Plugin;

trait String_Quoted_Printable_Decode {

    protected function string_quoted_printable_decode(string $string): string
    {
        return quoted_printable_decode($string);
    }

}