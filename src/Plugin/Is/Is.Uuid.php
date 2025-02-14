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

use Raxon\Module\Core;

trait Is_Uuid {

    protected function is_uuid(string $uuid=null): bool
    {
        return Core::is_uuid($uuid);
    }
}