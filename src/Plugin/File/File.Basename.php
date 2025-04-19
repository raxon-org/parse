<?php
namespace Plugin;

use Raxon\Module\File;

trait File_Basename {

    public function file_basename(string $url='', string $extension=''): string
    {
        return File::basename($url, $extension);
    }

}