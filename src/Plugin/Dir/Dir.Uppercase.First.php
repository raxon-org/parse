<?php
namespace Plugin;

use Raxon\Exception\DirectoryCreateException;
use Raxon\Module\Dir;

trait Dir_Uppercase_First {

    /**
     * @throws DirectoryCreateException
     */
    protected function dir_uppercase_first(string $directory): string
    {
        return Dir::ucfirst($directory);
    }

}