<?php
namespace Plugin;

trait String_Sha1 {

    protected function string_sha1(string $string, bool $raw_output=false): string
    {
        return sha1($string, $raw_output);
    }

}