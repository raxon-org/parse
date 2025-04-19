<?php
namespace Plugin;

trait Html_Specialchars {

    protected function html_specialchars(string $string='',  int $flags=ENT_COMPAT, string $encoding=null, bool $double_encode=true): string
    {
        if(is_string($flags)){
            $flags = constant($flags);
        }
        return htmlspecialchars($string, $flags, $encoding, $double_encode);
    }
}