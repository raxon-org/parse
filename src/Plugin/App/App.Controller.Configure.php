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
use Raxon\Module\Controller;

trait App_Controller_Configure {

    /**
     * @throws Exception
     */
    protected function app_controller_configure(string $caller): void
    {
        $object = $this->object();
        Controller::configure($object, $caller);
        d($object->config('controller'));
    }
}