<?php
namespace Plugin;

use Raxon\Module\File;

trait File_Size {

    public function file_size(string|null $url=null): int
    {
        return File::size($url);
    }

}