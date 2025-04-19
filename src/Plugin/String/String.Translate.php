<?php
namespace Plugin;

use Raxon\Module\Core;

trait String_Translate {

    protected function string_translate(string $string='', mixed $from, mixed $to=''): string
    {
        if(is_string($from)){
            $result = strtr($string, $from, $to);
        } else {
            $from = Core::object($from, 'array');
            $result = strtr($string, $from);
        }
        return $result;
    }

}