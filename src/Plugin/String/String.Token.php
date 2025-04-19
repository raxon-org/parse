<?php
namespace Plugin;

trait String_Token {

    protected function string_token(string $string, string $token=null): bool|string
    {
        if($token === null){
            $result = strtok($string);
        } else {
            $result = strtok($string, $token);
        }
        return $result;
    }

}