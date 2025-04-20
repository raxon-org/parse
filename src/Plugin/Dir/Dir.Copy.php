<?php
namespace Plugin;

use Raxon\Module\Dir;

trait Dir_Copy {

    protected function dir_copy(string $source, string $target): bool
    {
        return Dir::copy($source, $target);
    }

}