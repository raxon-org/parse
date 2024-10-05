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

trait Dd {

    /**
     * @throws ObjectException
     */
    public function dd($value): void
    {
        $object = $this->object();
        $tag = Core::object($object->config('package.raxon/parse.build.state.tag'), Core::OBJECT);
        if(property_exists($tag, 'tag')){
            if(
                property_exists($tag, 'line') &&
                is_object($tag->line) &&
                property_exists($tag->line, 'start')
            ){
                echo $tag->tag . PHP_EOL . ' on line ' . $tag->line->start . PHP_EOL;
            } else {
                echo $tag->tag . PHP_EOL . ' on line ' . $tag->line . PHP_EOL;
            }
        }
        dd($value);
    }

}