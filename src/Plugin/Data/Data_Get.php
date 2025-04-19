<?php
namespace Plugin;

use Raxon\Parse\Attribute\Argument;

trait Data_Get {

    #[Argument(apply: "literal", count: 1)]
    protected function data_get(string $attribute): mixed
    {
        $data = $this->data();
        $attribute = trim($attribute, '\'"');
        if(
            substr($attribute, 0, 1) === '$'
        ){
            $attribute = substr($attribute, 1);
        }
        return $data->data($attribute);
    }
}