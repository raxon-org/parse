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

trait Is_Nan {

    protected function is_nan(mixed $nan=null): bool
    {
        if(mb_strtolower($nan) == 'nan'){
            $nan = NAN;
        }
        return is_nan($nan);
    }
}