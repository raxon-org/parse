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
        if($value !== null){
            $this->object()->config($attribute, $value);
        }
        $object = $this->object();
        return $object->config($attribute);
    }

}