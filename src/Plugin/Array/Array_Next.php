<?php
namespace Plugin;

use Countable;

use Raxon\Parse\Attribute\Argument;

trait Array_Next {


    #[Argument(apply: "literal", count: 1)]
    protected function array_next(string $name=''): mixed
    {
        $name = trim($name, '\'"');
        if(substr($name, 0, 1) === '$'){
            $name = substr($name, 1);
        }
        $data = $this->data();
        $array = $data->get($name);
        $result = next($array);
        $data->set($name, $array);
        return $result;
    }
}