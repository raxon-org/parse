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

trait Is_Iterable {

    protected function is_iterable(mixed $iterable=null): bool
    {
        return is_iterable($iterable);
    }
}