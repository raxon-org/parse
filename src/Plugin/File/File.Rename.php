<?php
namespace Plugin;

use Raxon\Exception\FileMoveException;
use Raxon\Module\File;

trait File_Rename {

    /**
     * @throws FileMoveException
     */
    public function file_rename(string|null $source=null, string|null $destination=null, bool $overwrite=false): bool
    {
        return File::rename($source, $destination, $overwrite);
    }

}