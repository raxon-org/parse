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

trait Is_Empty {

    protected function is_empty(): bool
    {
        $attribute = func_get_args();
        ddd($attribute);
        foreach($attribute as $is_empty){
            if(!empty($is_empty)){
                return false;
            }
        }
        return true;
    }
}