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

trait App_Version_Minor {

    protected function app_version_minor(): ?string
    {
        $object = $this->object();
        return $object->config(Config::DATA_FRAMEWORK_VERSION_MINOR);
     }
}
