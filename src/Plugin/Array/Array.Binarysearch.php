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

    protected function array_binarysearch($sorted_array, $target, $multiple=true): false | int | array
    {
        if(!is_array($sorted_array)){
            if($multiple === true){
                return [];
            } else {
                return false;
            }
        }
        $low = 0;
        $high = count($sorted_array) - 1;
        $result = [];
        while ($low <= $high) {
            $mid = (int) floor(($low + $high) / 2);
            if ($sorted_array[$mid] === $target) {
                if($multiple === true){
                    for($i = $mid -1; $i > $low; $i--){
                        if($sorted_array[$i] === $target){
                            $result[] = $i;
                        } else {
                            break;
                        }
                    }
                    $result[] = $mid;
                    for ($i = $mid + 1; $i < $high; $i++) {
                        if ($sorted_array[$i] === $target) {
                            $result[] = $i;
                        } else {
                            break;
                        }
                    }
                    return $result;
                }
                return $mid;
            } elseif ($sorted_array[$mid] < $target) {
                $low = $mid + 1;
            } else {
                $high = $mid - 1;
            }
        }
        if($multiple === true){
            return $result;
        }
        return false;

    }

}