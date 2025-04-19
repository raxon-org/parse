<?php
namespace Plugin;

use Raxon\Parse\Attribute\Argument;

trait Array_Push {

    #[Argument(apply: "literal", count: 1)]
    protected function array_push(string $name='', mixed ...$value): mixed
    {
        $name = trim($name, '\'"');
        if(substr($name, 0, 1) === '$'){
            $name = substr($name, 1);
        }
        $data = $this->data();
        $array = $data->get($name);
        $result = array_push($array, ...$value);
        if($result !== false){
            $data->set($name, $array);
        }
        return $result;
    }
}