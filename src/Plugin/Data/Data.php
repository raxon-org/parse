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

use Raxon\Module\Filter;

use Exception;

trait Data {

    /**
     * @throws Exception
     */
    protected function data(string $attribute=null, mixed $value=null): mixed
    {
        $data = $this->data();
        if(
            $attribute === null &&
            $value === null
        ){
            return $data->data();
        }
        elseif(is_string($attribute)) {
            $attribute = trim($attribute, '\'"');
            if(
                substr($attribute, 0, 1) === '$'
            ){
                $attribute = substr($attribute, 1);
            }
            return $data->data($attribute, $value);
        }
        elseif($value === null){
            return $data->data($attribute);
        }
        return null;
    }
}