<?php
namespace Plugin\Property;

trait Property_Last {

    protected function property_last(object $object, array $allowed=[]): bool|int|string
    {
        $property = false;
        foreach($object as $attribute => $unused){
            $property = $attribute;
        }
        return $property;
    }
}