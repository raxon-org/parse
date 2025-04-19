<?php
namespace Plugin;

use Raxon\Module\Core;

trait Object_Clone {

    protected function object_clone(object $object): object
    {
        return Core::deep_clone($object);
    }
}