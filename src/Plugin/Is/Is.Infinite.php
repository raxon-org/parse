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

trait Is_Infinite {

    protected function is_infinite(float|null $float=null): bool
    {
        return is_infinite($float);
    }
}