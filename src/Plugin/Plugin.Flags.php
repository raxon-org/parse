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

trait Plugin_Flags {

    /**
     * @throws Exception
     */
    protected function plugin_flags(): array|object
    {
        $this->object();
        return Framework::flags($this->object());
    }
}