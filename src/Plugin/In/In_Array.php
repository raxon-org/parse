<?php
namespace Plugin;
/**
 * @package Plugin\Modifier
 * @author Remco van der Velde
 * @since 2024-08-19
 * @license MIT
 * @version 1.0
 * @changeLog
 *    - all
 */

use Exception;
use Raxon\App as Framework;

trait In_Array {

    /**
     * @throws Exception
     */
    protected function in_array(int|float|string  $needle='', array $haystack=[], $strict=false): bool
    {
        if(!empty($strict)){
            $result = in_array($needle, $haystack, true);
        } else {
            $result = in_array($needle, $haystack, false);
        }
        return $result;
    }
}