<?php
namespace Plugin;

use Raxon\Module\Dir;

trait Dir_Remove {

    protected function dir_remove(string $directory): bool
    {
        return Dir::remove($directory);
    }

}