<?php
namespace Plugin;

use Raxon\Exception\ObjectException;
use Raxon\Module\Core;

trait Object_Merge {

    /**
     * @throws ObjectException
     */
    protected function object_merge(object $object): object
    {
        $attribute = func_get_args();
        array_shift($attribute);
        foreach($attribute as $merge){
            $object = Core::object_merge($object, $merge);
        }
        return $object;
    }
}