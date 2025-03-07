<?php
namespace Plugin;

trait Route_Get {

    protected function route_get(string $name='', array $options=[]): array|object
    {
        $object = $this->object();
        return $object->route()::find($object, $name, $options);
    }
}