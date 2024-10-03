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

trait Data_Set {

    protected function data_set(string $attribute, mixed $value=null): void
    {
        $object = $this->object();
        $data = $this->data();
        if(
            is_string($attribute) &&
            substr($attribute, 0, 1) === '$'
        ){
            $attribute = substr($attribute, 1);
        }
        $data->set($attribute, $value);
    }
}