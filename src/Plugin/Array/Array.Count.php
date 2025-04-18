<?php
namespace Plugin;

use Countable;

trait Array_Count {

    protected function array_count(Countable | array $array, int $mode=COUNT_NORMAL): int
    {
        return count($array, $mode);
    }
}