<?php
namespace Plugin;

use Raxon\Module\File;

trait File_Count {

    public function file_count(string $directory, bool $include_directory=false): int
    {
        return File::count($directory, $include_directory);
    }

}