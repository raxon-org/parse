<?php
namespace Plugin;

use Raxon\Exception\DirectoryCreateException;
use Raxon\Module\Dir;

trait Dir_Name {

    /**
     * @throws DirectoryCreateException
     */
    protected function dir_name(string $directory, int $levels=null): string
    {
        return Dir::name($directory, $levels);
    }

}