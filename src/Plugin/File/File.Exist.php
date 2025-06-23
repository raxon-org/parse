<?php
namespace Plugin;

use Raxon\Module\File;

trait File_Exist {

    public function file_exist(string|null $url=null): bool
    {
        return File::exist($url);
    }

}