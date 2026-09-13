<?php
namespace Plugin;

use Exception;
use Raxon\Module\Data;

trait Object_Set {

    /**
     * @throws Exception
     */
    protected function object_set(object $object, string $property=null, mixed $value=null): object
    {
        $data = new Data($object);
        $data->set($property, $value);
        return $data->data();
    }
}