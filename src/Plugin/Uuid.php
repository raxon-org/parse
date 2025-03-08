<?php
namespace Plugin;

use Raxon\Module\Core;

trait Uuid {

    protected function uuid(): string
    {
        return Core::uuid();
    }

}