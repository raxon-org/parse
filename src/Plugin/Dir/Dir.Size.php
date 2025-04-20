<?php
namespace Plugin;

use Raxon\Exception\DirectoryCreateException;
use Raxon\Module\Dir;

trait Dir_Size {

    /**
     * @throws DirectoryCreateException
     */
    protected function dir_size(string $directory, bool $recursive=false): bool|int
    {
        return Dir::size($directory, $recursive);
    }

}