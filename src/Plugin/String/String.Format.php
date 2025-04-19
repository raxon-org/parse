<?php
namespace Plugin;

trait String_Format {

    protected function string_format(string $string, string $format): ?string
    {
        $attribute = func_get_args();
        array_shift($attribute);
        array_shift($attribute);
        $scan = sscanf($string, $format);
        if(count($attribute) > 0){
            $result = new stdClass();
            foreach($attribute as $key){
                $result->{$key} = array_shift($scan);
            }
        } else {
            $result = $scan;
        }
        return $result;
    }

}