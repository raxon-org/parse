<?php
namespace Plugin;

trait String_Md5 {

    protected function string_md5(string $string, bool $raw_output=false): string
    {
        return md5($string, $raw_output);
    }

}