<?php
namespace Plugin;

use Raxon\Module\Core;

trait Array_Unique {

    protected function array_unique(array $array, int|string $sort=SORT_STRING): object
    {
        if(is_string($sort)){
            $sort = constant($sort);
        }
        return array_unique($array, $sort);
    }
}