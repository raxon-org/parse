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

trait Plugin_default {

    function plugin_default(mixed $value, mixed $default=null){
        if(
            empty($value) &&
            $value !== 0 &&
            $value !== '0'
        ){
            return $default;
        }
        return $value;
    }

}