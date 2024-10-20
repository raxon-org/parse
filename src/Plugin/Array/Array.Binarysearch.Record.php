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

trait Array_Binarysearch_Record {

    protected function array_binarysearch_record(array $sorted_array, mixed $target, ?int &$count=0): false | int
    {
        if($count === 0){
            $count = count($sorted_array);
        }
        $low = 0;
        $high = $count - 1;
        while ($low <= $high) {
            $mid = (int) floor(($low + $high) / 2);
            if ($sorted_array[$mid] === $target) {
                return $mid;
            } elseif ($sorted_array[$mid] < $target) {
                $low = $mid + 1;
            } else {
                $high = $mid - 1;
            }
        }
        return false;
    }

}