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

use Raxon\MOdule\Core;

trait Is_Empty_Object {

    protected function is_empty_object(mixed $object=null): bool
    {
        $result = Core::object_is_empty($object);
        return $result;
    }
}