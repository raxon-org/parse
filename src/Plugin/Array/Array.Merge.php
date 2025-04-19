<?php
namespace Plugin;

trait Array_Merge {

    protected function array_merge(array $array=[], array ...$merge): bool
    {
        return array_merge($array, ...$merge);
    }
}