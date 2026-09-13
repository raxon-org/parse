<?php
namespace Plugin;

use Exception;
use Raxon\Module\Data;

trait Object_Get {

    /**
     * @throws Exception
     */
    protected function object_get(object $object, string $property=null): object
    {
        $data = new Data($object);
        return $data->get($property);
    }
}