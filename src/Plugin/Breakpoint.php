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
    public function breakpoint($value): void
    {
        $object = $this->object();
        $tag = $object->config('package.raxon/parse.build.state.tag');
        dd($tag);
        if(property_exists($tag, 'source')){
            if(
                property_exists($tag, 'line') &&
                is_object($tag->line) &&
                property_exists($tag->line, 'start')
            ){
                echo $tag->source . ':' . $tag->line->start . PHP_EOL;
            } else {
                echo $tag->source . ':' . $tag->line . PHP_EOL;
            }
        }
        breakpoint($value);
    }

}