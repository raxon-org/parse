<?php
namespace Plugin;

trait String_Tag_Strip {

    protected function string_tag_strip(string $string='', array|string $allowable_tags=null): string
    {
        if($allowable_tags === null){
            $result = strip_tags($string);
        } else {
            $result = strip_tags($string, $allowable_tags);
        }
        return $result;
    }

}