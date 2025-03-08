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

trait Data_Extract {

    /**
     * @throws Exception
     */
    protected function data_extract(string $attribute=''): mixed
    {
        if(
            is_string($attribute) &&
            substr($attribute, 0, 1) === '$'
        ){
            $attribute = substr($attribute, 1);
        }
        $data = $this->storage();
        return $data->extract($attribute);
    }
}