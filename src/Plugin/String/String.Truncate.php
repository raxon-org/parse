<?php
namespace Plugin;

trait String_Truncate {

    protected function string_truncate(string $value, int $length=80, string $replacement='...'): string
    {
        $replacement_length = strlen($replacement);
        $length = $length - $replacement_length;
        $value_length = strlen($value);
        if($value_length > $length){
            $value = substr($value, 0, $length) . $replacement;
        }
        return $value;
    }

}