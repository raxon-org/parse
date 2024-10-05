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
        d($tag);
        dd($value);
    }

}