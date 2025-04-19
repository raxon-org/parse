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

trait Session {

    protected function session(string $attribute, mixed $value=null): mixed
    {
        $object = $this->object();
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
                $object->session('delete', $value);
                return true;
            }
            $object->session($attribute, $value);
        }
        return $object->session($attribute);
    }
}