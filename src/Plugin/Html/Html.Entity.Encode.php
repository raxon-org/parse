<?php
namespace Plugin;

trait Html_Entity_Encode {

    protected function html_entity_encode(string $string='', int | string $flags = ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, string $encoding=null, bool $double_encoding=true): string
    {
        if(is_string($flags)){
            $flags = constant($flags);
        }
        return htmlentities($string, $flags, $encoding, $double_encoding);
    }
}