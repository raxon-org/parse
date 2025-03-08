<?php
/**
 * @package Plugin\Modifier
 * @author Remco van der Velde
 * @since 2024-08-19
 * @license MIT
 * @version 1.0
 * @changeLog
 *    - all
 */
namespace Plugin;

trait String_Replace {

    protected function string_replace(mixed $value='', mixed $search='', mixed $replace=''): mixed
    {
        if(is_array($value)){
            foreach($value as $key => $record){
                $value[$key] = str_replace($search, $replace, $record);
            }
            return $value;
        } else {
            return str_replace($search, $replace, $value);
        }
    }

}