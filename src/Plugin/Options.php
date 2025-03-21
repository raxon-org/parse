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