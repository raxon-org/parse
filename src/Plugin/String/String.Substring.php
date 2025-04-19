<?php
namespace Plugin;

trait String_Substring {

    protected function string_substring(string $string, int $offset=0, int $length=null, string $encoding=null): string
    {
        if(
            $length===null &&
            $encoding===null
        ){
            $result = mb_substr($string, $offset);
        } elseif($encoding ===null) {
            $result = mb_substr($string, $offset, $length);
        } else {
            $result = mb_substr($string, $offset, $length, $encoding);
        }
        return $result;
    }

}