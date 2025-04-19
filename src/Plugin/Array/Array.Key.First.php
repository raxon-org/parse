<?php
namespace Plugin;

trait Array_Key_First {

    protected function array_key_first(array $array=[]): null | int | string
    {
        return array_key_first($array);
    }
}