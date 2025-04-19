<?php
namespace Plugin;

trait String_Trim_Left {

    protected function string_trim_left(string $string='', string $mask=null): string
    {
        if($mask === null){
            $mask = " \t\n\r\0\x0B";
        }
        if(function_exists('mb_ltrim')){
            return mb_ltrim($string, $mask);
        }
        return ltrim($string, $mask);
    }

}