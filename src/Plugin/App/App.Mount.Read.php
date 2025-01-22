<?php
/**
 * @package Plugin\Modifier
 * @author Remco van der Velde
 * @since 2025-01-22
 * @license MIT
 * @version 1.0
 * @changeLog
 *    - all
 */
namespace Plugin;

use Exception;
use Raxon\App as Framework;

trait App_Mount_Read {

    /**
     * @throws Exception
     */
    protected function app_mount_read($flags, $options)
    {
        $this->object();
        d($flags);
        d($options);
    }
}