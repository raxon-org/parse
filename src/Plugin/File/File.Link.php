<?php
namespace Plugin;

use Raxon\Module\File;

trait File_Link {

    public function file_link(string|null $source=null, string|null $destination=null): bool
    {
        return File::link($source, $destination);
    }

}