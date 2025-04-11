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
use Raxon\Exception\TemplateException;

trait Plugin_constant {

    /**
     * @throws TemplateException
     */
    protected function plugin_constant(string $constant, mixed $value=null): mixed
    {
        $constant = mb_strtoupper($constant);
        if($value === null){
            try {
                return constant($constant);
            }
            catch(Error $error){
                $object = $this->object();
                $tag = $object->config('package.raxon/parse.build.state.tag');
                if(
                    array_key_exists('line', $tag) &&
                    array_key_exists('start', $tag['line'])
                ){
                    throw new TemplateException(
                        'Constant not defined: ' . $constant . ' on line ' . $tag['line']['start'],
                        $error->getCode(),
                        $error
                    );
                } elseif(array_key_exists('line', $tag)){
                    throw new TemplateException(
                    'Constant not defined: ' . $constant . ' on line ' . $tag['line'],
                        $error->getCode(),
                        $error
                    );
                } else {
                    throw new TemplateException(
                        'Constant not defined: ' . $constant,
                        $error->getCode(),
                        $error
                    );
                }
            }
        } else {
            define($constant, $value);
        }
        return null;
    }
}