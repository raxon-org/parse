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

use Raxon\Exception\ObjectException;
use Raxon\Module\Core;

trait Breakpoint {

    /**
     * @throws ObjectException
     */
    public function breakpoint($value, $options=[]): void
    {
        $object = $this->object();
        if(!array_key_exists('trace', $options)){
            $options['trace'] = true;
        }
        if($options['trace'] === true){
            $source = $object->config('package.raxon/parse.build.state.source.url');
            d($source);
            $tag = $object->config('package.raxon/parse.build.state.tag');
            if(property_exists($tag, 'source')){
                if(
                    property_exists($tag, 'line') &&
                    is_object($tag->line) &&
                    property_exists($tag->line, 'start')
                ){
                    $options['trace'] =  $source . ':' . $tag->line->start . PHP_EOL;
                } else {
                    $options['trace'] =  $source . ':' . $tag->line . PHP_EOL;
                }
            }
        }
        breakpoint($value, $options);
    }

}