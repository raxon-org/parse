<?php
namespace Plugin;

trait String_Uppercase_First {

    protected function string_uppercase_first(string $string): string
    {
        //php 8.4
        d($string);
        if(function_exists('mb_ucfirst')){
            $result =  mb_ucfirst($string);
        } else {
            $result = ucfirst($string);
        }
        d($result);
        return $result;
    }

}