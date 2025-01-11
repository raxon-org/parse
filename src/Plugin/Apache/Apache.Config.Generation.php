<?php
namespace Plugin;
/**
 * @package Plugin\Modifier
 * @author Remco van der Velde
 * @since 2024-08-19
 * @license MIT
 * @version 1.0
 * @changeLog
 *    - all
 */

use Exception;

use Raxon\App as Framework;

use Raxon\Module\Core;
use Raxon\Module\Dir;
use Raxon\Module\File;

trait Apache_Config_Generation {

    /**
     * @throws Exception
     */
    protected function apache_config_generation($options=[])
    {
        $start = microtime(true);
        $app = $this->object();
        $options = Core::object($options, Core::ARRAY);
        $dir = new Dir();
        $dir_mount_data = '/mnt/Vps3/Mount/Data/';
        $dir_mount_data_apache = $dir_mount_data . 'Apache/';
        $config = false;
        if(array_key_exists('config', $options)){
            $config = $options['config'];
        } else {
            throw new Exception('No config option given');
        }
        $read = $dir->read('/mnt/Disk2/', true);
        $duration = (microtime(true) - $start) * 1000;
        breakpoint(round($duration, 3) . ' msec');
        d($config);
        breakpoint($read);

        /*
        $uuid = false;
        if(!array_key_exists('url', $options)){
            $options['url'] = false;
        }
        if(!array_key_exists('language', $options)){
            $options['language'] = 'en';
        }
        if(!array_key_exists('sex', $options)){
            $options['sex'] = 'female';
        }
        if($options['url'] !== false){
            $url = $options['url'];
            $dir = Dir::name($url);
            Dir::Create($dir, Dir::CHMOD);
        } else {
            $dir = '/mnt/Disk2/Media/Voice/';
            $uuid = Core::uuid();
            $url = $dir . $uuid . '.wav';
        }
        $param = '';
        if($options['language'] === 'nl'){
            $param .= ' -v nl';
        } else {
            $param .= ' -v en';
        }
        if($options['sex'] === 'female'){
            $param .= '+f3';
        } else {
            $param .= '';
        }
        $command = 'espeak-ng ' . $param . ' -p 50 -s 120 -w ' . $url . ' "' . escapeshellarg($speak) . '"';
        exec($command);
        File::permission($app, [
            'dir' => $dir,
            'url' => $url
        ]);
        return (object) [
            'url' => $url,
            'dir' => $dir,
            'uuid' => $uuid,
        ];
        */
     }
}