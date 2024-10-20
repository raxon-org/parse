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

trait Array_Binarysearch_List {

    protected function array_binarysearch_list(array $sorted_array, mixed $target, ?int $count=0): array
    {
        $low = 0;
        if(
            $count === 0 ||
            $count === null
        ){
            $count = count($sorted_array);
        }
        $high = $count - 1;
        $result = [];
        while ($low <= $high) {
            $mid = (int) floor(($low + $high) / 2);
            if ($sorted_array[$mid] === $target) {
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
            } elseif ($sorted_array[$mid] < $target) {
                $low = $mid + 1;
            } else {
                $high = $mid - 1;
            }
        }
        return $result;
    }

}