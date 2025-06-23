<?php
namespace Plugin;

use Raxon\Module\File;

trait File_Mtime {

    public function file_mtime(string|null $url=null): bool|int|null
    {
        return File::mtime($url);
    }

}