<?php
namespace Plugin;

trait Url_Decode {

    protected function url_decode(string $value=''): string
    {
        return urldecode($value);
    }

}