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

use Countable;

trait Array_Count {

    protected function array_count(Countable | array $array, int $mode=COUNT_NORMAL): int
    {
        return count($array, $mode);
    }
}