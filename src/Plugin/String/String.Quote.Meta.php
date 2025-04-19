<?php
namespace Plugin;

trait String_Quote_Meta {

    protected function string_quote_meta(string $string): string
    {
        return quotemeta($string);
    }

}