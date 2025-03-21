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

trait Is_Array {

    protected function is_array(mixed $array=null): bool
    {
        return is_array($array);
    }
}