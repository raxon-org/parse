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

trait Implode {

    protected function implode(string $glue, array $array=null): string
    {
        trace();
        d($glue);
        $object = $this->object();
        d($object->config('package.raxon/parse.build.state'));
        return implode($glue, $array);
    }

}