<?php
namespace Plugin;

use Raxon\Module\Dir;
use Raxon\Module\File;

trait Dir_Read {

    protected function dir_read(string $directory='', bool $recursive=false, string $format='flat'): array
    {        
        if(File::exist($directory)){
            $dir = new Dir();
            return $dir->read($directory, $recursive, $format);
        }
        return [];
    }

}