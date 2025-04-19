<?php
namespace Plugin;

trait String_Uppercase_Word {

    protected function string_uppercase_word(string $string, string $delimiter=null): string
    {
        if(empty($delimiter)){
            $result = ucwords($string);
        } else {
            $result = ucwords($string, $delimiter);
        }
        return $result;
    }

}