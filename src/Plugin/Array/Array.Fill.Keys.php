<?php
namespace Plugin;

trait Array_Fill {

    protected function array_fill_keys(array $keys, mixed $value): array
    {
        return array_fill_keys($keys, $value);
    }
}