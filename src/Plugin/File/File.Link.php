<?php
namespace Plugin;

use Raxon\Module\File;

trait File_Link {

    public function file_link(string $source=null, string $destination=null): bool
    {
        return File::link($source, $destination);
    }

}