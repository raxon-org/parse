<?php
namespace Plugin;


trait Json_Decode {

    protected function json_decode(mixed $value, bool|null $associative=null, int $depth=512, mixed $flags=0): false | string
    {
        if(is_string($flags)){
            $flags = constant($flags);
        }
        elseif(is_numeric($flags)){
            $flags += 0;
        }
        return json_decode($value, $associative, $depth, $flags);        
    }
}