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
        if($value !== null){
            $object->config($attribute, $value);

        }
        return $object->config($attribute);
    }

}