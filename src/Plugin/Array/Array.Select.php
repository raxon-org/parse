<?php
namespace Plugin;

trait Array_Select {

    protected function array_select(array $array, int|string $key=0): mixed
    {
        if(array_key_exists($key, $array)){
            return $array[$key];
        }
        return null;
    }
}