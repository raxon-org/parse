<?php
namespace Plugin;

use Exception;
use Raxon\Module\File;

trait File_Copy {

    /**
     * @throws Exception
     */
    public function file_copy(string $source, string $destination): bool
    {
        return File::copy($source, $destination);
    }

}