<?php
/**
 * @package Plugin\Type
 * @author Remco van der Velde
 * @since 2024-08-19
 * @license MIT
 * @version 1.0
 * @changeLog
 *    - all
 */
namespace Plugin;

trait Type {

    public function type(mixed $value=null): string
    {
        ddd($value);
        return gettype($value);
    }

}