<?php
namespace Plugin;

use Countable;

trait Array_Current {

    protected function array_current(Countable | array $array): mixed
    {
        return current($array);
    }
}