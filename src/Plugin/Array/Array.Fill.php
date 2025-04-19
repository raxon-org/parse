<?php
namespace Plugin;

trait Array_Fill {

    protected function array_fill(int $start_index=0, int $count=1, mixed $value=null): array
    {
        return array_fill($start_index, $count, $value);
    }
}