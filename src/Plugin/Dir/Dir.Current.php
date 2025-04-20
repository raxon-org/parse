<?php
namespace Plugin;

use Raxon\Module\Dir;

trait Dir_Current {

    protected function dir_current(): string
    {
        return Dir::current();
    }

}