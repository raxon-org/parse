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

use Exception;
use Raxon\App as Framework;

trait Is_Array {

    /**
     * @throws Exception
     */
    protected function is_array(mixed $array=null): bool
    {
        return is_array($array);
    }
}