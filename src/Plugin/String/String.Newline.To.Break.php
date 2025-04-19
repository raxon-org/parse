<?php
namespace Plugin;

trait String_Newline_To_Break {

    protected function string_newline_to_break(string $string, bool $is_xhtml=true): string
    {
        return nl2br($string, $is_xhtml);
    }

}