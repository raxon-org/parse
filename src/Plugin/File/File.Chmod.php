<?php
namespace Plugin;

use Raxon\Module\File;

trait File_Chmod {

    public function file_chmod(string $url='', int $mode=0640): string
    {
        return File::chmod($url, $mode);
    }

}