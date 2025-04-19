<?php
namespace Plugin;

trait Html_Specialchars_Decode {

    protected function html_specialchars_decode(string $string='',  int $flags=ENT_COMPAT): string
    {
        if(is_string($flags)){
            $flags = constant($flags);
        }
        return htmlspecialchars_decode($string, $flags);
    }
}