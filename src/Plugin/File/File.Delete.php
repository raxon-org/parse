<?php
namespace Plugin;

use Raxon\Module\File;

trait File_Delete {

    public function file_delete(string $url): bool
    {
        return File::delete($url);
    }

}