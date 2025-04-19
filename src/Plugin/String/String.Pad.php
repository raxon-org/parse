<?php
namespace Plugin;

trait String_Pad {

    protected function string_pad(string $string, int $pad_length=0, string $pad_string=' ', $pad_type=STR_PAD_RIGHT): string
    {
        if(is_string($pad_type)){
            $pad_type = constant(strtoupper($pad_type));
        }
        return str_pad($string, $pad_length, $pad_string, $pad_type);
    }

}