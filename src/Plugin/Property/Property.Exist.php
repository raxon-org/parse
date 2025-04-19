<?php
namespace Plugin\Property;

trait Property_Exist {

    protected function property_exist(object $object, string $property): bool
    {
        return property_exists($object, $property);
    }
}