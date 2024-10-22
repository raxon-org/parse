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

trait Array_Binarysearch_Record {

    protected function array_binarysearch_record(array $sorted_array, mixed $target, ?int &$count=0, $operator=Filter::OPERATOR_STRICTLY_EQUAL, array &$search=[]): false | int
    {
        if(
            $count === 0 ||
            $count === null
        ){
            $count = count($sorted_array);
        }
        $low = 0;
        $high = $count - 1;
        $begin = null;
        $end = null;
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
                    if (
                        $sorted_array[$mid] === $target &&
                        !in_array($mid, $search, true)
                    ) {
                        $search[] = $mid;
                        return $mid;
                    } elseif ($sorted_array[$mid] < $target) {
                        $low = $mid + 1;
                    } else {
                        $high = $mid - 1;
                    }
                break;
                case '==' :
                case Filter::OPERATOR_EXACT :
                case Filter::OPERATOR_EQUAL :
                    if (
                        $sorted_array[$mid] == $target &&
                        !in_array($mid, $search, true)
                    ) {
                        $search[] = $mid;
                        return $mid;
                    } elseif ($sorted_array[$mid] < $target) {
                        $low = $mid + 1;
                    } else {
                        $high = $mid - 1;
                    }
                break;
                case '>' :
                case Filter::OPERATOR_GREATER_THAN :
                    if (
                        $sorted_array[$mid] > $target &&
                        !in_array($mid, $search, true)
                    ) {
                        $search[] = $mid;
                        return $mid;
                    } elseif ($sorted_array[$mid] < $target) {
                        $low = $mid + 1;
                    } else {
                        $high = $mid - 1;
                    }
                break;
                case '>=' :
                case Filter::OPERATOR_GREATER_THAN_EQUAL :
                    if (
                        $sorted_array[$mid] >= $target &&
                        !in_array($mid, $search, true)
                    ) {
                        $search[] = $mid;
                        return $mid;
                    } elseif ($sorted_array[$mid] < $target) {
                        $low = $mid + 1;
                    } else {
                        $high = $mid - 1;
                    }
                break;
                case '<' :
                case Filter::OPERATOR_LOWER_THAN :
                    if (
                        $sorted_array[$mid] < $target &&
                        !in_array($mid, $search, true)
                    ) {
                        $search[] = $mid;
                        return $mid;
                    } elseif ($sorted_array[$mid] < $target) {
                        $low = $mid + 1;
                    } else {
                        $high = $mid - 1;
                    }
                break;
                case '<=' :
                case Filter::OPERATOR_LOWER_THAN_EQUAL :
                    if (
                        $sorted_array[$mid] <= $target &&
                        !in_array($mid, $search, true)
                    ) {
                        $search[] = $mid;
                        return $mid;
                    } elseif ($sorted_array[$mid] < $target) {
                        $low = $mid + 1;
                    } else {
                        $high = $mid - 1;
                    }
                break;
                case '!=' :
                case Filter::OPERATOR_NOT_EQUAL :
                case Filter::OPERATOR_NOT_EXACT :
                    if (
                        $sorted_array[$mid] != $target &&
                        !in_array($mid, $search, true)
                    ) {
                        $search[] = $mid;
                        return $mid;
                    } elseif ($sorted_array[$mid] < $target) {
                        $low = $mid + 1;
                    } else {
                        $high = $mid - 1;
                    }
                    break;
                case '!==' :
                case Filter::OPERATOR_NOT_STRICTLY_EQUAL :
                case Filter::OPERATOR_NOT_STRICTLY_EXACT :
                    if (
                        $sorted_array[$mid] !== $target &&
                        !in_array($mid, $search, true)
                    ) {
                        $search[] = $mid;
                        return $mid;
                    } elseif ($sorted_array[$mid] < $target) {
                        $low = $mid + 1;
                    } else {
                        $high = $mid - 1;
                    }
                    break;
                case '> <' :
                case Filter::OPERATOR_BETWEEN :
                    if (
                        $sorted_array[$mid] > $begin &&
                        $sorted_array[$mid] < $end &&
                        !in_array($mid, $search, true)
                    ) {
                        $search[] = $mid;
                        return $mid;
                    }
                    elseif ($sorted_array[$mid] <= $begin) {
                        $low = $mid + 1;
                    }
                    elseif ($sorted_array[$mid] >= $end) {
                        $high = $mid - 1;
                    }
                    else {
                        $high = $mid - 1;
                    }
                break;
                case '>=<' :
                case Filter::OPERATOR_BETWEEN_EQUALS :
                    if (
                        $sorted_array[$mid] >= $begin &&
                        $sorted_array[$mid] <= $end &&
                        !in_array($mid, $search, true)
                    ) {
                        $search[] = $mid;
                        return $mid;
                    }
                    elseif ($sorted_array[$mid] < $begin) {
                        $low = $mid + 1;
                    }
                    elseif ($sorted_array[$mid] > $end) {
                        $high = $mid - 1;
                    }
                    else {
                        $high = $mid - 1;
                    }
                    break;
                case Filter::OPERATOR_STRICTLY_START:
                    if(
                        mb_substr($sorted_array[$mid], 0, mb_strlen($target)) === $target &&
                        !in_array($mid, $search, true)
                    ){
                        $search[] = $mid;
                        return $mid;
                    }
                    elseif (mb_substr($sorted_array[$mid], 0, mb_strlen($target)) < $target) {
                        $low = $mid + 1;
                    } else {
                        $high = $mid - 1;
                    }
                break;
                case Filter::OPERATOR_START:
                    if(
                        mb_strtolower(mb_substr($sorted_array[$mid], 0, mb_strlen($target))) === mb_strtolower($target) &&
                        !in_array($mid, $search, true)
                    ){
                        $search[] = $mid;
                        return $mid;
                    }
                    elseif (mb_strtolower(mb_substr($sorted_array[$mid], 0, mb_strlen($target))) < mb_strtolower($target)) {
                        $low = $mid + 1;
                    } else {
                        $high = $mid - 1;
                    }
                break;
            }
        }
        return false;
    }

}