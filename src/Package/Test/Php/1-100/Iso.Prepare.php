<?php
/**
 * @author          Remco van der Velde
 * @since           2020-10-27
 * @version         1.0
 * @changeLog
 *     -    all
 */

use Raxon\App;
use Raxon\Config;
use Raxon\Module\Core;
use Raxon\Module\Data;
use Raxon\Module\Dir;
use Raxon\Module\File;

use Raxon\Exception\LocateException;
use Raxon\Exception\ObjectException;

$dir = __DIR__;
$dir_vendor =
    DIRECTORY_SEPARATOR .
    'Application' .
    DIRECTORY_SEPARATOR .
    'vendor' .
    DIRECTORY_SEPARATOR;

$autoload = $dir_vendor . 'autoload.php';
$autoload = require $autoload;
try {
    $config = new Config(
        [
            'dir.vendor' => $dir_vendor,
            'time.start' => microtime(true),
        ]
    );
    $app = new App($autoload, $config);

    $dir = new Dir();

    $read = $dir->read('/mnt/Vps3/', true);

    $target_dir = '/mnt/Disk2/Media/Backup/Vps-2024-12-04/';
    $target_prefix = 'Vps3-data-';
    $tree = 'tree' . $app->config('extension.json');
    $tree = $target_dir . $tree;
    $data = new Data();
    foreach($read as $nr => $file){
        $file->size = File::size($file->url);
        if($file->size > (2 * 1024 * 1024 * 1024)){
            continue;
        }
        $file->uuid = Core::uuid();
        $data->set('Tree.' . $file->uuid, $file);
    }
    breakpoint($data);
    $target = $target_dir . $target_prefix . $nr . '.gzip';
    // then iso split in 1 GB parts and go with the flow.

    /*
    foreach($read as $nr => $file) {
        $explode = explode('.', $file->url);
        $extension = array_pop($explode);
        $file->new = false;
        if(strtoupper($extension) === 'WMA'){
            $file->new = implode('.', $explode) . '.mp3';
        }
        elseif(strtoupper($extension) === 'WAV'){
            $file->new = implode('.', $explode) . '.mp3';
        }
        if($file->new !== false){
            if(File::exist($file->new)){
                continue;
            }
            $command = 'ffmpeg -i \'' . $file->url . '\' -vn -ar 44100 -ac 2 -ab 320k -f mp3 \'' . $file->new . '\'';
            exec($command);
        }
    }
    /*
    $result = App::run($app);
    if(is_scalar($result)){
        echo $result;
    }
    elseif(is_array($result)) {
        echo implode(PHP_EOL, $result);
    }
    */
} catch (Exception | LocateException | ObjectException $exception) {
    echo $exception;
}


