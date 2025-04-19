<?php
namespace Plugin;

trait Array_Reverse {

    protected function array_reverse(array $array, bool $preserve_key): object
    {
        return array_reverse($array, $preserve_key);
    }
}