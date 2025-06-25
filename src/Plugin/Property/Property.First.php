<?php
namespace Plugin\Property;

trait Property_First {

    protected function property_first(object $object): bool|int|string
    {
        foreach($object as $attribute => $unused){
            return $attribute;
        }
        return false;
    }
}