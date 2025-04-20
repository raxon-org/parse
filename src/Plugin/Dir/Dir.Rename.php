<?php
namespace Plugin;

use Raxon\Module\Dir;

trait Dir_Rename {

    protected function dir_rename(string $source, string $destination, bool $overwrite=false): bool
    {
        return Dir::rename($source, $destination, $overwrite);
    }

}