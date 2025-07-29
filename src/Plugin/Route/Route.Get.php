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
        if(is_object($name)){
            ddd($name);
        }        
        return $object->route()::find($object, $name, $options);
    }
}