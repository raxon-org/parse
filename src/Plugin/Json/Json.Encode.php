<?php
namespace Plugin;


trait Json_Encode {

    protected function json_encode(mixed $value, mixed $flags=0, int $depth=512): false | string
    {
        if(is_string($flags)){
            $flags = constant($flags);
        }
        elseif(is_numeric($flags)){
            $flags += 0;
        }
        return json_encode($value, $flags, $depth);
    }

}