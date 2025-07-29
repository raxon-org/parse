<?php
namespace Plugin;

use Exception;

trait Route_Get {

    /**
     * @throws Exception
     */
    protected function route_get(string|object $name='', array $options=[]): bool | string
    {
        $object = $this->object();
        if($name === ''){
            trace();
        }             
        return $object->route()::find($object, $name, $options);
    }
}