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

use Raxon\Module\Core;
use Raxon\Module\Dir;
use Raxon\Module\File;

trait App_Speak {

    /**
     * @throws Exception
     */
    protected function app_speak($speak='', $options=[])
    {
        $app = $this->object();
        $options = Core::object($options, Core::ARRAY);
        if(!array_key_exists('url', $options)){
            $options['url'] = false;
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
        $command = 'espeak-ng -v en -w ' . $url . ' "' . escapeshellarg($speak) . '"';
        exec($command);
        File::permission($app, [
            'dir' => $dir,
            'url' => $url
        ]);
    }
}