<?php
namespace Plugin;

use Countable;

trait Array_Current {

    protected function array_current(Countable | array $array, int $mode=COUNT_NORMAL): int
    {
        return count($array, $mode);
    }
}