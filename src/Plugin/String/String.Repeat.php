<?php
namespace Plugin;

trait String_Repeat {

    protected function string_repeat(string $input, $multiplier=0): string
    {
        $multiplier = abs($multiplier);
        return str_repeat($input, $multiplier);
    }

}