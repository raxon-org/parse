<?php
namespace Plugin;

use Raxon\Parse\Attribute\Argument;

trait Data_Set {

    #[Argument(apply: "literal", count: 1)]
    protected function data_set(string $attribute, mixed $value=null): void
    {
        $object = $this->object();
        $data = $this->storage();
        if(
            is_string($attribute) &&
            substr($attribute, 0, 1) === '$'
        ){
            $attribute = substr($attribute, 1);
        }
        $data->set($attribute, $value);
    }
}