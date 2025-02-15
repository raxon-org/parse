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

trait Is_Float {

    protected function is_float(mixed $float=null): bool
    {
        return is_float($float);
    }
}