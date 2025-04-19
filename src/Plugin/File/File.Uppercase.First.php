<?php
namespace Plugin;

use Raxon\Module\File;

trait File_Uppercase_First {

    public function file_uppercase_first(string $url): string
    {
        return File::ucfirst($url);
    }

}