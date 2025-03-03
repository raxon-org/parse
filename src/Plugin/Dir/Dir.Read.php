<?php
/**
 * @package Plugin
 * @author Remco van der Velde
 * @since 2024-08-19
 * @license MIT
 * @version 1.0
 * @changeLog
 *    - all
 */

use Raxon\Module\Dir;
use Raxon\Module\File;

trait Dir_Read {

    protected function dir_read(string $directory='', $recursive=false, $format='flat'): array
    {
        if(File::exist($directory)){
            $dir = new Dir();
            return $dir->read($directory, $recursive, $format);
        }
        return [];
    }

}