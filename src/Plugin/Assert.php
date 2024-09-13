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

use Throwable;

trait Assert {

    /**
     * @throws Throwable
     */
    protected function assert(mixed $assertion, Throwable|string|null $description=null): null | bool
    {
        if($description == null){
            return (bool) $assertion;
        }
        if($assertion !== true){
            throw new $description('Assertion failed');
        }
        return null;
    }

}