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

trait Array_Keys {

    protected function array_keys(array $array, int $flags=SORT_NATURAL, bool $strict=false): bool
    {
        return array_keys($array, $flags, $strict);
    }
}