<?php
namespace Plugin\Property;

trait Property_Count {

    protected function property_count(object $object): int
    {
        return count(get_object_vars($object));
    }
}