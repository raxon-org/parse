<?php
namespace Plugin;

trait Password_Rehash_Need {


    protected function password_rehash_need(string $hash='', int|string $algorithm=PASSWORD_DEFAULT, array $options=null): bool
    {
        if(is_string($algorithm)){
            $algorithm = constant($algorithm);
        }
        if(!is_array($options)){
            $result = password_needs_rehash($hash, $algorithm);
        } else {
            $result = password_needs_rehash($hash, $algorithm, $options);
        }
        return $result;
    }
}