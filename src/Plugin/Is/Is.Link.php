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

trait Is_Link {

    protected function is_link(string $link=null): bool
    {
        return File::is_link($link);
    }
}