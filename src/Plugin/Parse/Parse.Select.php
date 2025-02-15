<?php
namespace Plugin;
/**
 * @package Plugin
 * @author Remco van der Velde
 * @since 2025-02-15
 * @license MIT
 * @version 1.0
 * @changeLog
 *    - all
 */


use Raxon\Module\Core;

use Raxon\Exception\ObjectException;
use Raxon\Exception\FileWriteException;

use Exception;

trait Parse_Select {

    /**
     * @throws Exception
     * @throws FileWriteException
     * @throws ObjectException
     */
    protected function parse_select(string $url='', string $select='', string $scope='scope:object'): mixed
    {
        $data = $this->data();
        $parse = $this->parse();
        return Core::object_select($parse, $data, $url, $select, true, $scope);
    }
}