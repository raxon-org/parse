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
use Raxon\Config;

use Raxon\Module\Dir;
use Raxon\Module\File;

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
        $dir_ramdisk = $app->config('ramdisk.url');
        $dir_ramdisk_user = $dir_ramdisk . '33' . $app->config('ds'); //need all users so webservice can read
        $dir_ramdisk_mount = $dir_ramdisk_user . 'Mount' . $app->config('ds');
        $file_name = hash('sha256', $mount) . $app->config('extension.json');
        $url = $dir_ramdisk_mount . $file_name;
        breakpoint($url);
        Dir::create($dir_ramdisk_mount, Dir::CHMOD);
        File::permission($app, [
            'ramdisk' => $dir_ramdisk,
            'user' => $dir_ramdisk_user,
            'mount' => $dir_ramdisk_mount,
        ]);

//        ddd($read);
    }
}