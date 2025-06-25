<?php
namespace Plugin;

trait Preg_Quote {

    protected function preg_quote(string $string='', string|null $delimiter=null): string
    {
        if($delimiter !== null){
            $result = preg_quote($string, $delimiter);
        } else {
            $result = preg_quote($string);
        }
        return $result;
    }
}