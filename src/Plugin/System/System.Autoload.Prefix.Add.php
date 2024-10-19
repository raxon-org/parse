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

use Raxon\App;
use Raxon\App as Framework;
use Raxon\Module\Autoload;

trait System_Autoload_Prefix_Add {

    protected function system_autoload_prefix_add($prefix='', $directory='', $extension=''): void
    {
        $directory = str_replace(':', DIRECTORY_SEPARATOR, $directory);
        $object = $this->object();
        $autoload = $object->data(App::NAMESPACE . '.' . Autoload::NAME . '.' . App::RAXON);
        $autoload->addPrefix($prefix, $directory, $extension);
    }

}