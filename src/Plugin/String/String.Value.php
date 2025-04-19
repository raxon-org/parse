<?php
namespace Plugin;

trait String_Value {

    protected function string_value(mixed $variable): string
    {
        return strval($variable);
    }

}