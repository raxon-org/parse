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

trait Array_Keys {

    protected function array_keys(array $array, mixed $filter_value=null, bool $strict=false): array
    {
        if($filter_value !== null){
            return array_keys($array, $filter_value, $strict);
        }
        elseif($strict === true){
            return array_keys($array,  null, true);
        }
        return array_keys($array);
    }
}