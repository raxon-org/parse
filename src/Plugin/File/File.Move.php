<?php
namespace Plugin;

use Raxon\Exception\FileMoveException;
use Raxon\Module\File;

trait File_Move {

    /**
     * @throws FileMoveException
     */
    public function file_move(string $source=null, string $destination=null, bool $overwrite=false): bool
    {
        return File::move($source, $destination, $overwrite);
    }

}