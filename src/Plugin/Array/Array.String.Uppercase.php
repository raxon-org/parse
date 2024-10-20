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

trait Array_String_Uppercase {

    protected function array_string_uppercase(object &$array): bool
    {
        foreach($array as $key => $value){
            $array[$key] = mb_strtoupper($value);
            if(is_object($value) || is_array($value)){
                $array[$key] = $this->array_string_lowercase($value);
            }
        }
        $multiple = true;
        return true;
    }

}