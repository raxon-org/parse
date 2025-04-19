<?php
namespace Plugin;

use Raxon\Module\File;

trait File_Extension_Remove {

    public function file_extension_remove(string $url=null, array $extension=[]): string
    {
        return File::extension_remove($url, $extension);
    }

}