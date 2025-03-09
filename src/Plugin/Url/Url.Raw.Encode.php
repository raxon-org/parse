<?php
namespace Plugin;

trait Url_Raw_Encode {

    protected function url_raw_encode(string $value=''): string
    {
        return rawurlencode($value);
    }

}