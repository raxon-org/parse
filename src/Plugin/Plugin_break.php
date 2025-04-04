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

trait Plugin_break {

    function plugin_break(int $depth=1): void
    {
        $object = $this->object();
        $object->config('package.raxon/parse.build.break', $depth);
    }

}