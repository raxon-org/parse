<?php
namespace Plugin;

use Raxon\Parse\Attribute\Argument;

trait Cookie {

    #[Argument(apply: "literal", count: 1)]
    protected function cookie(string $attribute, mixed $value=null, int $duration=null): mixed
    {
        $object = $this->object();
        if(
            is_string($attribute) &&
            substr($attribute, 0, 1) === '$'
        ){
            $attribute = substr($attribute, 1);
        }
        return $object->cookie($attribute, $value, $duration);
    }
}