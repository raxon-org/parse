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

trait Is_Array_Nested {

    protected function is_array_nested(mixed $array=null): bool
    {
        return Core::is_array_nested($array);
    }
}