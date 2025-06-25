<?php
namespace Plugin;

trait Array_Key_Exist {

    protected function array_key_exist(array $array=[], int|string|null $key=null): bool
    {
        return array_key_exists($key, $array);
    }
}