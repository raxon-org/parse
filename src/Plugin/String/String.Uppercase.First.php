<?php
namespace Plugin;

trait String_Uppercase_First {

    protected function string_uppercase_first(string $string): string
    {
        //php 8.4
        d($string);
        if(function_exists('mb_ucfirst')){
            return mb_ucfirst($string);
        } else {
            return ucfirst($string);
        }

    }

}