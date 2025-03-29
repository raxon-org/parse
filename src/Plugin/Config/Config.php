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

use Raxon\Parse\Attribute\Validate;

trait Config {

    #[Validate(
        argument: [
            "is.string && (length:1,255 || contains:['delete','remove'] || is.email || is.uuid)",
            "is.string and (length:1,255 or contains:['delete','remove'] or is.email or is.uuid)",
            "mixed",
        ],
        result: "mixed"
    )]
    protected function config(string $attribute, mixed $value=null): mixed
    {
        $object = $this->object();
//        breakpoint($object->config());
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