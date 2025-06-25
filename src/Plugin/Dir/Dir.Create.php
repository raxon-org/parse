<?php
namespace Plugin;

use Raxon\Module\Dir;

trait Dir_Create {

    protected function dir_create(string $url, int|null $chmod=null): bool
    {
        return Dir::create($url, $chmod);
    }

}