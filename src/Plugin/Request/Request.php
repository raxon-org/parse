<?php
namespace PLugin;
/**
 * @package Plugin
 * @author Remco van der Velde
 * @since 2025-02-22
 * @license MIT
 * @version 1.0
 * @changeLog
 *    - all
 */


use Raxon\App;

use Raxon\Module\Route;

trait Request {

    protected function request($attribute=null, $value=null): mixed
    {
        $object = $this->object();
        if($attribute !== null){
            if($value === null){
                return $object->request($attribute);
            } else {
                $object->request($attribute, $value);
            }
        } else {
            return $object->request();
        }
        return null;
    }
}