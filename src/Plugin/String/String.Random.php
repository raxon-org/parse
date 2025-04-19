<?php
namespace Plugin;

trait String_Random {

    protected function string_random(int $length=1): string
    {
        $length += 0;
        $result = '';
        for($i=0; $i < $length; $i++){
            $char = rand(32, 126);
            $char = chr($char);
            $result .= $char;
        }
        return $result;
    }

}