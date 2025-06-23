<?php
namespace Plugin;

use Raxon\Exception\FileMoveException;
use Raxon\Module\File;

trait File_Move {

    /**
     * @throws FileMoveException
     */
    public function file_move(string|null $source=null, string|null $destination=null, bool $overwrite=false): bool
    {
        return File::move($source, $destination, $overwrite);
    }

}