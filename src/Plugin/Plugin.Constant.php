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

use Error;

trait Plugin_constant {

    protected function plugin_constant(string $constant, mixed $value=null): mixed
    {
        $constant = mb_strtoupper($constant);
        if($value === null){
            try {
                return constant($constant);
            }
            catch(Error $error){
                $object = $this->object();
                ddd($object->config('package.raxon/parse.build.state.tag'));
                ddd($error);
            }
        } else {
            define($constant, $value);
        }
        return null;
    }
}