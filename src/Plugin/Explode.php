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

    protected function explode(string $separator, string $string=null, int $limit=null): false | array
    {
        if($limit !== null){
            return explode($separator, $string, $limit);

        }
        return explode($separator, $string);
    }

}