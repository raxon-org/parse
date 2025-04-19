<?php
namespace Plugin;

trait Php_Version {

    protected function php_version(): string
    {
        return PHP_VERSION;
    }
}