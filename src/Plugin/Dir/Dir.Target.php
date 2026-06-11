<?php
namespace Plugin;

trait Dir_Target {

    protected function dir_target(string $directory): string
    {
        return str_replace('\'', '\\\'', $directory);
    }

}