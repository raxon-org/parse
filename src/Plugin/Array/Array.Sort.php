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

use Raxon\App;
use Raxon\App as Framework;
use Raxon\Module\Autoload;

trait Array_Sort {

    protected function array_sort($array, $order='ASC', $flags=SORT_NATURAL, &$index=[]): array
    {
        d($index);
        breakpoint($array);
        return $array;
    }

}