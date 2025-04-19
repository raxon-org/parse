<?php
namespace Plugin;

trait Array_Key {

    protected function array_key(string $name=''):  int | null | string
    {
        $name = trim($name, '\'"');
        if(substr($name, 0, 1) === '$'){
            $name = substr($name, 1);
        }
        $data = $this->data();
        $array = $data->get($name);
        $result = key($array);
        if($result !== false){
            $data->set($name, $array);
        }
        return $result;
    }
}