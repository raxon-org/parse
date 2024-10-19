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

    protected function array_binarysearch($sorted_array, $target): bool | array
    {
        if(!is_array($sorted_array)){
            return false;
        }
        $low = 0;
        $high = count($sorted_array) - 1;
        $result = [];
        while ($low <= $high) {
            $mid = (int) floor(($low + $high) / 2);
            if ($sorted_array[$mid] === $target) {
                $result[] = $mid;
                $low = $mid + 1;
            } elseif ($sorted_array[$mid] < $target) {
                $low = $mid + 1;
            } else {
                $high = $mid - 1;
            }
        }
        if(array_key_exists(0, $result)){
            return $result[0];
        }
        return false;
    }

}