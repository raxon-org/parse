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

trait Config {

    protected function config(string $attribute, mixed $value=null): mixed
    {
        $object = $this->object();
        breakpoint($object->config());
        if($value !== null){
            if(
                in_array(
                    $attribute,
                    [
                        'delete',
                        'remove'
                    ],
                    true
                )
            ){
                $object->config($attribute, $value);
                return true;
            }
            return $object->config($attribute, $value);
        }
        return $object->config($attribute);
    }
}