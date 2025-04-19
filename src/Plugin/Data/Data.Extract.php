<?php
namespace Plugin;

use Exception;
use Raxon\Parse\Attribute\Argument;

trait Data_Extract {

    /**
     * @throws Exception
     */
    #[Argument(apply: "literal", count: 1)]
    protected function data_extract(string $attribute=''): mixed
    {
        $attribute = trim($attribute, '\'"');
        if(
            is_string($attribute) &&
            substr($attribute, 0, 1) === '$'
        ){
            $attribute = substr($attribute, 1);
        }
        $data = $this->storage();
        return $data->extract($attribute);
    }
}