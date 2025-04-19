<?php
namespace Plugin;

trait String_Quoted_Printable_Encode {

    protected function string_quoted_printable_encode(string $string): string
    {
        return quoted_printable_encode($string);
    }

}