<?php
namespace Plugin;

use Countable;

trait Array_Next {

    protected function array_next(Countable | array &$array): mixed
    {
        return next($array);
    }
}