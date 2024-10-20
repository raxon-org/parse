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

trait Array_Asort {

    protected function array_asort(array &$array, int $flags=SORT_NATURAL): bool
    {
        asort($array, $flags);
        return true;
    }
}