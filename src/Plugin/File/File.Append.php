<?php
namespace Plugin;

use Raxon\Exception\FileAppendException;
use Raxon\Module\File;

trait File_Append {

    /**
     * @throws FileAppendException
     */
    public function file_append(string $url=null, string $append=''): bool|int
    {
        return File::append($url, $append);
    }

}