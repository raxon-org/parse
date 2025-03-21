<?php
namespace Plugin;
/**
 * @package Plugin
 * @author Remco van der Velde
 * @since 2025-02-22
 * @license MIT
 * @version 1.0
 * @changeLog
 *    - all
 */

use Raxon\Module\File;

trait File_Exist {

    public function file_exist(string $url=null): bool
    {
        return File::exist($url);
    }

}