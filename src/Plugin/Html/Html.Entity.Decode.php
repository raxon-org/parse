<?php
namespace Plugin;

trait Html_Entity_Decode {

    protected function html_entity_decode(string $string='', int | string $flags = ENT_QUOTES | ENT_SUBSTITUTE, string $encoding=null): string
    {
        if(is_string($flags)){
            $flags = constant($flags);
        }
        return html_entity_decode($string, $flags, $encoding);
    }
}