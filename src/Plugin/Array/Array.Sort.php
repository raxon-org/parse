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

trait Array_Sort {

    protected function array_sort(array &$array, int $flags=SORT_NATURAL): bool
    {
        sort($array, $flags);
        return true;
    }

}