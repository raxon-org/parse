<?php
namespace Plugin;

trait Plugin_Data {

    protected function plugin_data(string $attribute=null, mixed $value=null): mixed
    {
        $data = $this->data();
        $attribute = trim($attribute, '\'"');
        if(
            substr($attribute, 0, 1) === '$'
        ){
            $attribute = substr($attribute, 1);
        }
        if($value !== null){
            $data->data($attribute, $value);
        }
        return $data->data($attribute);
    }
}