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

use Raxon\Config;

trait App_Built {

    protected function app_built()
    {
        $object = $this->object();
        return $object->config(Config::DATA_FRAMEWORK_BUILT);
     }
}