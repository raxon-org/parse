<?php
namespace Plugin;

use Raxon\Module\Core;

trait Array_Object {

    protected function array_object(array $array): object
    {
        return Core::array_object($array);
    }
}