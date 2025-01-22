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
use Raxon\Module\Dir;

trait App_Mount_Read {

    /**
     * @throws Exception
     */
    protected function app_mount_read($flags, $options)
    {
        $this->object();
        d($flags);
        d($options);
        $mount = $options->mount ?? false;
        if(!Dir::is($mount)){
            throw new Exception('Mount is not a directory');
        }
        $dir = new Dir();
        $read = $dir->read($mount, true);
        $app = $this->object();
        $dir_temp = $app->config();
        breakpoint($dir_temp);

//        ddd($read);
    }
}