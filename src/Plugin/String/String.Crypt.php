<?php
namespace Plugin;

trait String_Crypt {

    protected function string_crypt(string $string, string $salt): ?string
    {
        return crypt($string, $salt);
    }

}