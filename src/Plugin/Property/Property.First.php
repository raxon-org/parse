<?php
namespace Plugin\Property;

trait Property_First {

    protected function property_first(object $object): bool|string
    {
        foreach($object as $attribute => $unused){
            return $attribute;
        }
        return false;
    }
}