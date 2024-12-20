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

use Exception;
use Raxon\App as Framework;

trait Is_Empty {

    /**
     * @throws Exception
     */
    protected function is_empty(): bool
    {
        $attribute = func_get_args();
        foreach($attribute as $is_empty){
            if(!empty($is_empty)){
                return false;
            }
        }
        return true;
    }
}