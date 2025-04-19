<?php
namespace Plugin;

trait String_Crc32 {

    protected function string_crc32(string $string): string
    {
        return crc32($string);
    }

}