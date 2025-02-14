<?php
/**
 * @package Plugin\Modifier
 * @author Remco van der Velde
 * @since 2024-08-19
 * @license MIT
 * @version 1.0
 * @changeLog
 *    - all
 */
namespace Plugin;

use Raxon\Module\File;

trait Is_File {

    protected function is_file(string $url=null): bool
    {
        return File::is($url);
    }
}