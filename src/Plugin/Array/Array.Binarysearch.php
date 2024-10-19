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

trait Array_Binarysearch {

    protected function array_binarysearch($arr, $target): bool | int
    {
        $low = 0;
        $high = count($arr) - 1;
        while ($low <= $high) {
            $mid = floor(($low + $high) / 2);
            if ($arr[$mid] == $target) {
                return $mid; // element found, return key
            } elseif ($arr[$mid] < $target) {
                $low = $mid + 1;
            } else {
                $high = $mid - 1;
            }
        }
        return false; // element not found
    }

}