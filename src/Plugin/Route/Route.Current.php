<?php
namespace Plugin;

use Exception;
use Raxon\Parse\Attribute\Argument;

trait Route_Current {

    #[Argument(apply: "literal", count: 1)]
    protected function route_current(string $attribute=null): mixed
    {
        $object = $this->object();
        if($attribute !== null){
            $attribute = trim($attribute, '\'"');
            if(substr($attribute, 0, 1) == '$'){
                $attribute = substr($attribute, 1);
            }
            $current = $object->route()->current();
            if(property_exists($current, $attribute)){
                return $current->{$attribute};
            }
        } else {
            return $object->route()->current();
        }
        return null;
    }
}