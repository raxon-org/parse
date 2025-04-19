<?php
namespace Plugin;

trait String_Char {

    protected function string_char(int $codepoint): string
    {
        return chr($codepoint);
    }

}