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

trait Explode {

    protected function explode(string $separator, mixed $string=null, int $limit=null): false | array
    {
        return explode($separator, $string, $limit);
    }

}