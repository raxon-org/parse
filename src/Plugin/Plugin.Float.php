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

use Raxon\Exception\ObjectException;
use Raxon\Module\Core;

trait Plugin_Float {

    /**
     * @throws ObjectException
     */
    function plugin_float(mixed $value){
        return (float) $value;
    }

}