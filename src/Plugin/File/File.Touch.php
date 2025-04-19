<?php
namespace Plugin;

use Raxon\Module\File;

trait File_Touch {

    public function file_touch(string $url, int $time=null, int $atime=null): bool|int|null
    {
        return File::touch($url, $time, $atime);
    }

}