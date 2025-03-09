<?php
namespace Plugin;

trait Url_Raw_Decode {

    protected function url_raw_decode(string $value=''): string
    {
        return rawurldecode($value);
    }

}