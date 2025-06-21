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

use Raxon\Module\Core;

use Raxon\Exception\ObjectException;

trait Plugin_object {

    /**
     * @throws ObjectException
     */
    protected function plugin_object(mixed $input=null, string $output=Core::OBJECT, string|null $type=null){
        return Core::object($input, $output, $type);
    }

}