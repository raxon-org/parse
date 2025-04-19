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

use Raxon\Parse\Attribute\Argument;

trait Array_Cycles {

    #[Argument(apply: "literal", count: 1)]
    protected function array_cycles(string $name='', array $arguments=[]): array
    {
        $name = trim($name, '\'"');
        if(substr($name, 0, 1) === '$'){
            $name = substr($name, 1);
        }
        $data = $this->data();
        $attribute = 'raxon.org.cycle.' . $name;
        $array = $data->get($attribute);
        if(
            $array &&
            is_array($array)
        ){
            $next = next($array);
            if($next === false){
                $next = reset($array);
            }
            $data->set($attribute, $array);
            return $next;
        } else {
            $data->set($attribute, $arguments);
            return reset($arguments);
        }
        return $result;
    }
}