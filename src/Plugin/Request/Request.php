<?php
namespace Plugin;

trait Request {

    protected function request(string|null $attribute=null, mixed $value=null): mixed
    {
        $object = $this->object();
        if($value !== null){
            $object->request($attribute, $value);
        }
        return $object->request($attribute);
    }
}