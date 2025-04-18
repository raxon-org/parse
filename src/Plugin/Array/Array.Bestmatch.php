<?php
namespace Plugin;

use Countable;

use Raxon\Module\Core;

trait Array_Bestmatch {

    protected function array_bestmatch(Countable | array $array, string $search='', bool $with_score = false): mixed
    {
        return Core::array_bestmatch($array, $search, $with_score);
    }
}