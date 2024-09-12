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

trait Assert {

    protected function assert(mixed $assertion, Throwable|string|null $description=null): bool
    {
        if($description == null){
            return $assertion;
        }
        if($assertion === false){
            throw new $description('Assertion failed');
        }
        return true;
    }

}