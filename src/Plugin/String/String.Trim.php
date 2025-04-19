<?php
namespace Plugin;

trait String_Trim {

    protected function string_trim(string $string='', string $mask=null): string
    {
        if($mask === null){
            $mask = " \t\n\r\0\x0B";
        }
        if(function_exists('mb_trim')){
            return mb_trim($string, $mask);
        }
        return trim($string, $mask);
    }

}