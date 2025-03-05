<?php
/**
 * @package Plugin
 * @author Remco van der Velde
 * @since 2024-08-19
 * @license MIT
 * @version 1.0
 * @changeLog
 *    - all
 */


trait Json_Encode {

    protected function json_encode(mixed $value, mixed $flags, int $depth=512): false | string
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