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

trait Is_Debug {

    protected function is_debug(boolean $is_debug=null): bool
    {
        $object = $this->object();
        if($is_debug === null){
            return $object->data('is.debug');
        } else {
            return $object->data('is.debug', $is_debug);
        }
    }
}