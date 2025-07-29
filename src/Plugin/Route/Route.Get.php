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
        $find = $object->route()::find($object, $name, $options);
        if($find === false || $find === ''){
            d($name);
            trace();
            die('here');
        }
        return $find;
    }
}