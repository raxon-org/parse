<?php
namespace Plugin;

use Exception;

trait Route_Get {

    /**
     * @throws Exception
     */
    protected function route_get(string $name='', array $options=[]): bool | string
    {
        $object = $this->object();
        return $object->route()::find($object, $name, $options);
    }
}