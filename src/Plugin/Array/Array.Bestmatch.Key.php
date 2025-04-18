<?php
namespace Plugin;

use Countable;

use Raxon\Module\Core;

trait Array_Bestmatch_Key {

    protected function array_bestmatch_key(Countable | array $array, string $search=''): bool | int | null | string
    {
        return Core::array_bestmatch_key($array, $search);
    }
}