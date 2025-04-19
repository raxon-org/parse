<?php
namespace Plugin\Property;

trait Property_List {

    protected function property_list(object $object): bool|string
    {
        $result = [];
        if(is_object($object)){
            foreach($object as $attribute => $unused){
                if(empty($allowed)){
                    $result[] = $attribute;
                }
                elseif(
                    in_array(
                        $attribute,
                        $allowed,
                        true
                    )
                ){
                    $result[] = $attribute;
                }
            }
        }
        return $result;
    }
}