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

use Raxon\Module\Dir;

trait Is_Dir {

    protected function is_dir(string $url=null): bool
    {
        return Dir::is($url);
    }
}