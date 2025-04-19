<?php
namespace Plugin;

trait String_Trim_Right {

    protected function string_trim_right(string $string='', string $mask=null): string
    {
        if($mask === null){
            $mask = " \t\n\r\0\x0B";
        }
        if(function_exists('mb_rtrim')){
            return mb_rtrim($string, $mask);
        }
        return rtrim($string, $mask);
    }

}