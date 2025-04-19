<?php
namespace Plugin;

use Raxon\Parse\Attribute\Argument;

trait String_Replace_Case_Insensitive {

    #[Argument(apply: "literal", count: 1, index:3)]
    protected function string_replace(array|string $search='', array|string $replace='', array|string $subject='', string $attribute=null): array|string
    {
        if(!empty($attribute)){
            $attribute = trim($attribute, '\'"');
            if(substr($attribute, 0, 1) == '$'){
                $attribute = substr($attribute, 1);
            }
            $count = 0;
            $subject = str_ireplace($search, $replace, $subject, $count);
            $data = $this->data();
            $data->data($attribute, $count);
        } else {
            $subject = str_ireplace($search, $replace, $subject);
        }
        return $subject;
    }

}