<?php
namespace Plugin;

trait Array_Key_Last {

    protected function array_key_last(array $array=[]): null | int | string
    {
        return array_key_last($array);
    }
}