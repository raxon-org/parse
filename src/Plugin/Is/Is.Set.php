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

trait Is_Set {

    protected function is_set(): bool
    {
        $attribute = func_get_args();
        foreach($attribute as $is_set){
            if(!isset($is_set)){
                return false;
            }
        }
        return true;
    }
}