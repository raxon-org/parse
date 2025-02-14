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

trait Parameter {

    /**
     * @throws Exception
     */
    protected function parameter(string $name='', int $offset=null): mixed
    {
        $object = $this->object();
        return Framework::parameter($object, $name, $offset);
    }
}