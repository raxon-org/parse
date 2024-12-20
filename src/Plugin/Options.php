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

use Raxon\App as Framework;

use Exception;

trait Options {

    /**
     * @throws Exception
     */
    protected function options($type=''): array|object
    {
        $this->object();
        return Framework::options($this->object(), $type);
    }
}