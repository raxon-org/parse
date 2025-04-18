<?php
namespace Plugin;

use Countable;

use Raxon\Parse\Attribute\Argument;

trait Array_Next {


    #[Argument(apply: "literal", count: 1)]
    protected function array_next(string $array): mixed
    {
        ddd($array);
        return next($array);
    }
}