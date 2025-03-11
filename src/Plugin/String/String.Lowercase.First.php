<?php
namespace Plugin;


trait String_Lowercase_First {

    protected function string_lowercase_first(string $string): string
    {
        //php 8.4
        if(function_exists('mb_lcfirst')){
            return mb_lcfirst($string);
        } else {
            return lcfirst($string);
        }

    }

}