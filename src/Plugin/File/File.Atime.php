<?php
namespace Plugin;

use Raxon\Module\File;

trait File_Atime {

    public function file_atime(string $url=null): bool|int|null
    {
        return File::atime($url);
    }

}