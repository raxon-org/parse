<?php
namespace Plugin;

use Raxon\Parse\Attribute\Argument;

trait Data_Delete {

    #[Argument(apply: "literal", count: 1)]
    protected function data_delete(string $attribute): void
    {
        $attribute = trim($attribute, '\'"');
        if(substr($attribute, 0, 1) === '$'){
            $attribute = substr($attribute, 1);
        }
        $data = $this->data();
        $data->delete($attribute);
    }
}