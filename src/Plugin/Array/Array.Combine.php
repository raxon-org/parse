<?php
namespace Plugin;

trait Array_Combine {

    protected function array_combine(array $keys=[], array $values=[]): array
    {
        return array_combine($keys, $values);
    }
}