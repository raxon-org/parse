<?php
/**
 * @package Plugin\Modifier
 * @author Remco van der Velde
 * @since 2025-02-11
 * @license MIT
 * @version 1.0
 * @changeLog
 *    - all
 */
namespace Plugin;

use Exception;
use Raxon\App as Framework;

trait App_Controller_Configure {

    /**
     * @throws Exception
     */
    protected function app_controller_configure(string $caller): void
    {
        ddd($caller);
    }
}