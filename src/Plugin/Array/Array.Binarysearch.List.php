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

use Raxon\Module\Filter;

trait Array_Binarysearch_List {

    protected function array_binarysearch_list(array $sorted_array, mixed $target, $operator=Filter::OPERATOR_STRICTLY_EQUAL, ?int &$count=0,   ?int $limit=0, ?int $offset=0): array
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
        $begin = null;
        $end = null;
        $found = 0;
        if(
            in_array(
                $operator,
                [
                    Filter::OPERATOR_BETWEEN,
                    Filter::OPERATOR_BETWEEN_EQUALS
                ],
                true
            )
        ){
            if(is_array($target) && count($target) === 2){
                $begin = $target[0];
                $end = $target[1];
            }
            elseif(is_string($target)){
                $explode = explode('..', $target, 2);
                if (array_key_exists(1, $explode)) {
                    if (is_numeric($explode[0])) {
                        $explode[0] += 0;
                    }
                    if (is_numeric($explode[1])) {
                        $explode[1] += 0;
                    }
                    $begin = $explode[0];
                    $end = $explode[1];
                }
            }
        }
        while ($low <= $high) {
            $mid = (int) floor(($low + $high) / 2);
            switch($operator){
                case '===':
                case Filter::OPERATOR_STRICTLY_EXACT:
                case Filter::OPERATOR_STRICTLY_EQUAL:
                    if ($sorted_array[$mid] === $target) {
                        for($i = $mid -1; $i > $low; $i--){
                            if($sorted_array[$i] === $target){
                                if(
                                    $found >= $offset &&
                                    $found < $offset + $limit
                                ){
                                    $result[] = $i;
                                }
                                elseif($found >= $offset + $limit){
                                    break;
                                }
                                $found++;
                            } else {
                                break;
                            }
                        }
                        if(
                            $found >= $offset &&
                            $found < $offset + $limit
                        ){
                            $result[] = $i;
                        }
                        elseif($found >= $offset + $limit){
                            return $result;
                        }
                        $found++;
                        for ($i = $mid + 1; $i < $high; $i++) {
                            if ($sorted_array[$i] === $target) {
                                if(
                                    $found >= $offset &&
                                    $found < $offset + $limit
                                ){
                                    $result[] = $i;
                                }
                                elseif($found >= $offset + $limit){
                                    break;
                                }
                                $found++;
                            } else {
                                break;
                            }
                        }
                        return $result;
                    }
                    elseif ($sorted_array[$mid] < $target) {
                        $low = $mid + 1;
                    } else {
                        $high = $mid - 1;
                    }
                break;
                case '==' :
                case Filter::OPERATOR_EXACT :
                case Filter::OPERATOR_EQUAL :
                    if ($sorted_array[$mid] == $target) {
                        for($i = $mid -1; $i > $low; $i--){
                            if($sorted_array[$i] == $target){
                                if(
                                    $found >= $offset &&
                                    $found < $offset + $limit
                                ){
                                    $result[] = $i;
                                }
                                elseif($found >= $offset + $limit){
                                    break;
                                }
                                $found++;
                            } else {
                                break;
                            }
                        }
                        if(
                            $found >= $offset &&
                            $found < $offset + $limit
                        ){
                            $result[] = $i;
                        }
                        elseif($found >= $offset + $limit){
                            return $result;
                        }
                        $found++;
                        for ($i = $mid + 1; $i < $high; $i++) {
                            if ($sorted_array[$i] == $target) {
                                if(
                                    $found >= $offset &&
                                    $found < $offset + $limit
                                ){
                                    $result[] = $i;
                                }
                                elseif($found >= $offset + $limit){
                                    break;
                                }
                                $found++;
                            } else {
                                break;
                            }
                        }
                        return $result;
                    }
                    elseif ($sorted_array[$mid] < $target) {
                        $low = $mid + 1;
                    } else {
                        $high = $mid - 1;
                    }
                break;
                case '>' :
                case Filter::OPERATOR_GREATER_THAN :
                    if ($sorted_array[$mid] > $target) {
                        for($i = $mid -1; $i > $low; $i--){
                            if($sorted_array[$i] > $target){
                                if(
                                    $found >= $offset &&
                                    $found < $offset + $limit
                                ){
                                    $result[] = $i;
                                }
                                elseif($found >= $offset + $limit){
                                    break;
                                }
                                $found++;
                            } else {
                                break;
                            }
                        }
                        if(
                            $found >= $offset &&
                            $found < $offset + $limit
                        ){
                            $result[] = $i;
                        }
                        elseif($found >= $offset + $limit){
                            return $result;
                        }
                        $found++;
                        for ($i = $mid + 1; $i < $high; $i++) {
                            if ($sorted_array[$i] > $target) {
                                if(
                                    $found >= $offset &&
                                    $found < $offset + $limit
                                ){
                                    $result[] = $i;
                                }
                                elseif($found >= $offset + $limit){
                                    break;
                                }
                                $found++;
                            } else {
                                break;
                            }
                        }
                        return $result;
                    }
                    elseif ($sorted_array[$mid] < $target) {
                        $low = $mid + 1;
                    } else {
                        $high = $mid - 1;
                    }
                break;
                case '>=' :
                case Filter::OPERATOR_GREATER_THAN_EQUAL :
                    if ($sorted_array[$mid] >= $target) {
                        for($i = $mid -1; $i > $low; $i--){
                            if($sorted_array[$i] >= $target){
                                if(
                                    $found >= $offset &&
                                    $found < $offset + $limit
                                ){
                                    $result[] = $i;
                                }
                                elseif($found >= $offset + $limit){
                                    break;
                                }
                                $found++;
                            } else {
                                break;
                            }
                        }
                        if(
                            $found >= $offset &&
                            $found < $offset + $limit
                        ){
                            $result[] = $i;
                        }
                        elseif($found >= $offset + $limit){
                            return $result;
                        }
                        $found++;
                        for ($i = $mid + 1; $i < $high; $i++) {
                            if ($sorted_array[$i] >= $target) {
                                if(
                                    $found >= $offset &&
                                    $found < $offset + $limit
                                ){
                                    $result[] = $i;
                                }
                                elseif($found >= $offset + $limit){
                                    break;
                                }
                                $found++;
                            } else {
                                break;
                            }
                        }
                        return $result;
                    }
                    elseif ($sorted_array[$mid] < $target) {
                        $low = $mid + 1;
                    } else {
                        $high = $mid - 1;
                    }
                break;
                case '<' :
                case Filter::OPERATOR_LOWER_THAN :
                if ($sorted_array[$mid] < $target) {
                    for($i = $mid -1; $i > $low; $i--){
                        if($sorted_array[$i] < $target){
                            if(
                                $found >= $offset &&
                                $found < $offset + $limit
                            ){
                                $result[] = $i;
                            }
                            elseif($found >= $offset + $limit){
                                break;
                            }
                            $found++;
                        } else {
                            break;
                        }
                    }
                    if(
                        $found >= $offset &&
                        $found < $offset + $limit
                    ){
                        $result[] = $i;
                    }
                    elseif($found >= $offset + $limit){
                        return $result;
                    }
                    $found++;
                    for ($i = $mid + 1; $i < $high; $i++) {
                        if ($sorted_array[$i] < $target) {
                            if(
                                $found >= $offset &&
                                $found < $offset + $limit
                            ){
                                $result[] = $i;
                            }
                            elseif($found >= $offset + $limit){
                                break;
                            }
                            $found++;
                        } else {
                            break;
                        }
                    }
                    return $result;
                }
                elseif ($sorted_array[$mid] < $target) {
                    $low = $mid + 1;
                } else {
                    $high = $mid - 1;
                }
                break;
                case '<=' :
                case Filter::OPERATOR_LOWER_THAN_EQUAL :
                    if ($sorted_array[$mid] <= $target) {
                        for($i = $mid -1; $i > $low; $i--){
                            if($sorted_array[$i] <= $target){
                                if(
                                    $found >= $offset &&
                                    $found < $offset + $limit
                                ){
                                    $result[] = $i;
                                }
                                elseif($found >= $offset + $limit){
                                    break;
                                }
                                $found++;
                            } else {
                                break;
                            }
                        }
                        if(
                            $found >= $offset &&
                            $found < $offset + $limit
                        ){
                            $result[] = $i;
                        }
                        elseif($found >= $offset + $limit){
                            return $result;
                        }
                        $found++;
                        for ($i = $mid + 1; $i < $high; $i++) {
                            if ($sorted_array[$i] <= $target) {
                                if(
                                    $found >= $offset &&
                                    $found < $offset + $limit
                                ){
                                    $result[] = $i;
                                }
                                elseif($found >= $offset + $limit){
                                    break;
                                }
                                $found++;
                            } else {
                                break;
                            }
                        }
                        return $result;
                    }
                    elseif ($sorted_array[$mid] < $target) {
                        $low = $mid + 1;
                    } else {
                        $high = $mid - 1;
                    }
                break;
                case '!=' :
                case Filter::OPERATOR_NOT_EQUAL :
                case Filter::OPERATOR_NOT_EXACT :
                    if ($sorted_array[$mid] != $target) {
                        for($i = $mid -1; $i > $low; $i--){
                            if($sorted_array[$i] != $target){
                                if(
                                    $found >= $offset &&
                                    $found < $offset + $limit
                                ){
                                    $result[] = $i;
                                }
                                elseif($found >= $offset + $limit){
                                    break;
                                }
                                $found++;
                            } else {
                                break;
                            }
                        }
                        if(
                            $found >= $offset &&
                            $found < $offset + $limit
                        ){
                            $result[] = $i;
                        }
                        elseif($found >= $offset + $limit){
                            return $result;
                        }
                        $found++;
                        for ($i = $mid + 1; $i < $high; $i++) {
                            if ($sorted_array[$i] != $target) {
                                if(
                                    $found >= $offset &&
                                    $found < $offset + $limit
                                ){
                                    $result[] = $i;
                                }
                                elseif($found >= $offset + $limit){
                                    break;
                                }
                                $found++;
                            } else {
                                break;
                            }
                        }
                        return $result;
                    }
                    elseif ($sorted_array[$mid] < $target) {
                        $low = $mid + 1;
                    } else {
                        $high = $mid - 1;
                    }
                break;
                case '!==' :
                case Filter::OPERATOR_NOT_STRICTLY_EQUAL :
                case Filter::OPERATOR_NOT_STRICTLY_EXACT :
                    if ($sorted_array[$mid] !== $target) {
                        for($i = $mid -1; $i > $low; $i--){
                            if($sorted_array[$i] !== $target){
                                if(
                                    $found >= $offset &&
                                    $found < $offset + $limit
                                ){
                                    $result[] = $i;
                                }
                                elseif($found >= $offset + $limit){
                                    break;
                                }
                                $found++;
                            } else {
                                break;
                            }
                        }
                        if(
                            $found >= $offset &&
                            $found < $offset + $limit
                        ){
                            $result[] = $i;
                        }
                        elseif($found >= $offset + $limit){
                            return $result;
                        }
                        $found++;
                        for ($i = $mid + 1; $i < $high; $i++) {
                            if ($sorted_array[$i] !== $target) {
                                if(
                                    $found >= $offset &&
                                    $found < $offset + $limit
                                ){
                                    $result[] = $i;
                                }
                                elseif($found >= $offset + $limit){
                                    break;
                                }
                                $found++;
                            } else {
                                break;
                            }
                        }
                        return $result;
                    }
                    elseif ($sorted_array[$mid] < $target) {
                        $low = $mid + 1;
                    } else {
                        $high = $mid - 1;
                    }
                break;
                case '> <' :
                case Filter::OPERATOR_BETWEEN :
                    if(
                        $sorted_array[$mid] > $begin &&
                        $sorted_array[$mid] < $end
                    ){
                        for($i = $mid -1; $i > $low; $i--){
                            if(
                                $sorted_array[$mid] > $begin &&
                                $sorted_array[$mid] < $end
                            ){
                                if(
                                    $found >= $offset &&
                                    $found < $offset + $limit
                                ){
                                    $result[] = $i;
                                }
                                elseif($found >= $offset + $limit){
                                    break;
                                }
                                $found++;
                            } else {
                                break;
                            }
                        }
                        if(
                            $found >= $offset &&
                            $found < $offset + $limit
                        ){
                            $result[] = $i;
                        }
                        elseif($found >= $offset + $limit){
                            return $result;
                        }
                        $found++;
                        for ($i = $mid + 1; $i < $high; $i++) {
                            if (
                                $sorted_array[$mid] > $begin &&
                                $sorted_array[$mid] < $end
                            ) {
                                if(
                                    $found >= $offset &&
                                    $found < $offset + $limit
                                ){
                                    $result[] = $i;
                                }
                                elseif($found >= $offset + $limit){
                                    break;
                                }
                                $found++;
                            } else {
                                break;
                            }
                        }
                        return $result;
                    }
                    elseif ($sorted_array[$mid] < $target) {
                        $low = $mid + 1;
                    } else {
                        $high = $mid - 1;
                    }
                break;
                case '>=<' :
                case Filter::OPERATOR_BETWEEN_EQUALS :
                    if(
                        $sorted_array[$mid] >= $begin &&
                        $sorted_array[$mid] <= $end
                    ){
                        for($i = $mid -1; $i > $low; $i--){
                            if(
                                $sorted_array[$mid] >= $begin &&
                                $sorted_array[$mid] <= $end
                            ){
                                if(
                                    $found >= $offset &&
                                    $found < $offset + $limit
                                ){
                                    $result[] = $i;
                                }
                                elseif($found >= $offset + $limit){
                                    break;
                                }
                                $found++;
                            } else {
                                break;
                            }
                        }
                        if(
                            $found >= $offset &&
                            $found < $offset + $limit
                        ){
                            $result[] = $i;
                        }
                        elseif($found >= $offset + $limit){
                            return $result;
                        }
                        $found++;
                        for ($i = $mid + 1; $i < $high; $i++) {
                            if (
                                $sorted_array[$mid] >= $begin &&
                                $sorted_array[$mid] <= $end
                            ) {
                                if(
                                    $found >= $offset &&
                                    $found < $offset + $limit
                                ){
                                    $result[] = $i;
                                }
                                elseif($found >= $offset + $limit){
                                    break;
                                }
                                $found++;
                            } else {
                                break;
                            }
                        }
                        return $result;
                    }
                    elseif ($sorted_array[$mid] < $target) {
                        $low = $mid + 1;
                    } else {
                        $high = $mid - 1;
                    }
                break;
                case Filter::OPERATOR_STRICTLY_START :
                    if(mb_substr($sorted_array[$mid], 0, mb_strlen($target)) === $target){
                        for($i = $mid -1; $i > $low; $i--){
                            if(mb_substr($sorted_array[$i], 0, mb_strlen($target)) === $target){
                                if(
                                    $found >= $offset &&
                                    $found < $offset + $limit
                                ){
                                    $result[] = $i;
                                }
                                $found++;
                            } else {
                                break;
                            }
                        }
                        if(
                            $found >= $offset &&
                            $found < $offset + $limit
                        ){
                            $result[] = $i;
                        }
                        elseif($found >= $offset + $limit){
                            return $result;
                        }
                        $found++;
                        for ($i = $mid + 1; $i < $high; $i++) {
                            if (mb_substr($sorted_array[$i], 0, mb_strlen($target)) === $target) {
                                if(
                                    $found >= $offset &&
                                    $found < $offset + $limit
                                ){
                                    $result[] = $i;
                                }
                                elseif($found >= $offset + $limit){
                                    break;
                                }
                                $found++;
                            } else {
                                break;
                            }
                        }
                        return $result;
                    }
                    elseif (mb_substr($sorted_array[$mid], 0, mb_strlen($target)) < $target) {
                        $low = $mid + 1;
                    } else {
                        $high = $mid - 1;
                    }
                break;
                case Filter::OPERATOR_START :
                    if(mb_strtolower(mb_substr($sorted_array[$mid], 0, mb_strlen($target))) === mb_strtolower($target)){
                        for($i = $mid -1; $i > $low; $i--){
                            if(mb_strtolower(mb_substr($sorted_array[$i], 0, mb_strlen($target))) === mb_strtolower($target)){
                                if(
                                    $found >= $offset &&
                                    $found < $offset + $limit
                                ){
                                    $result[] = $i;
                                }
                                elseif($found >= $offset + $limit){
                                    break;
                                }
                                $found++;
                            } else {
                                break;
                            }
                        }
                        if(
                            $found >= $offset &&
                            $found < $offset + $limit
                        ){
                            $result[] = $i;
                        }
                        elseif($found >= $offset + $limit){
                            return $result;
                        }
                        $found++;
                        for ($i = $mid + 1; $i < $high; $i++) {
                            if (mb_strtolower(mb_substr($sorted_array[$i], 0, mb_strlen($target))) === mb_strtolower($target)) {
                                if(
                                    $found >= $offset &&
                                    $found < $offset + $limit
                                ){
                                    $result[] = $i;
                                }
                                elseif($found >= $offset + $limit){
                                    break;
                                }
                                $found++;
                            } else {
                                break;
                            }
                        }
                        return $result;
                    }
                    elseif (mb_strtolower(mb_substr($sorted_array[$mid], 0, mb_strlen($target))) < mb_strtolower($target)) {
                        $low = $mid + 1;
                    } else {
                        $high = $mid - 1;
                    }
                break;
            }
        }
        return $result;
    }

}