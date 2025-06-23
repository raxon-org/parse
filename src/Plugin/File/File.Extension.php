<?php
namespace Plugin;

use Raxon\Module\File;

trait File_Extension {

    public function file_extension(string|null $url=null): string
    {
        return File::extension($url);
    }

}