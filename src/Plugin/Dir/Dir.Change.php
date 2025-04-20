<?php
namespace Plugin;

use Raxon\Exception\DirectoryCreateException;
use Raxon\Module\Dir;

trait Dir_Change {

    /**
     * @throws DirectoryCreateException
     */
    protected function dir_change(string $directory): string
    {
        return Dir::change($directory);
    }

}