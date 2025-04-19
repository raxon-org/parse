<?php
namespace Plugin;

trait Array_Key_List {

    protected function array_key_list(array $array=[], int|string $search=null, bool $strict = true): bool
    {
        if($search === null){
            $result = array_keys($array);
        } else {
            $result = array_keys($array, $search, $strict);
        }
        return $result;
    }
}