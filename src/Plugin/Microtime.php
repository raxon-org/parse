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

trait Microtime {

    protected function microtime(bool $as_float=true): float | string
    {
        return microtime($as_float);
    }

}