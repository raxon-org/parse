<?php
namespace Plugin;

use Countable;

use Raxon\Module\Core;

trait Array_Bestmatch_List {

    protected function array_bestmatch_list(Countable | array $array, string $search='', bool $with_score = false): array|bool
    {
        return Core::array_bestmatch_list($array, $search, $with_score);
    }
}