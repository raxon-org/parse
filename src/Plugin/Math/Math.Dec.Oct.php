<?php
namespace Plugin;

trait Math_Dec_Oct {

    protected function math_dec_oct(string|int $string): string
    {
        return decoct((int)$string);
    }
}