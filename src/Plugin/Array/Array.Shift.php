<?php
namespace Plugin;

use Raxon\Parse\Attribute\Argument;

trait Array_Shift {

    #[Argument(apply: "literal", count: 1)]
    protected function array_shift(string $name=''): mixed
    {
        $name = trim($name, '\'"');
        if(substr($name, 0, 1) === '$'){
            $name = substr($name, 1);
        }
        $data = $this->data();
        $array = $data->get($name);
        $result = array_shift($array);
        if($result !== false){
            $data->set($name, $array);
        }
        return $result;
    }
}