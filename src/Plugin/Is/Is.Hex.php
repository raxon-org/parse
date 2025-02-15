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

trait Is_Hex {

    protected function is_hex(mixed $hex=null): bool
    {
        if(
            is_string($hex) &&
            mb_strtolower($hex) == 'nan'
        ){
            $hex = NAN;
        }
        return ctype_xdigit($hex);
    }
}