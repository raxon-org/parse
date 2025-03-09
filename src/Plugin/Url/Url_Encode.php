<?php
namespace Plugin;

trait Url_Encode {

    protected function url_encode(string $value=''): string
    {
        return urlencode($value);
    }

}