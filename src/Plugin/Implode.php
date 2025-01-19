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

    protected function implode(string $glue, mixed $array=null): string
    {
        return implode($glue, $array);
    }

}