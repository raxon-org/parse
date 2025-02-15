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

trait Is_Scalar {

    protected function is_scalar(mixed $scalar=null): bool
    {
        return is_scalar($scalar);
    }
}