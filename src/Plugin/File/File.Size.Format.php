<?php
namespace Plugin;

use Raxon\Module\File;

trait File_Size_Format {

    public function file_size_format(float|int $size=0): string
    {
        return File::size_format((int) $size);
    }

}