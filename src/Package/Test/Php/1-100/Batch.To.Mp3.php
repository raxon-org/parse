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

    $read = $dir->read('/mnt/Disk2/Media/Music/', true);
    foreach($read as $nr => $file) {
        $explode = explode('.', $file->url);
        $extension = array_pop($explode);
        $file->new = false;
        $file->temp = false;
        if(strtoupper($extension) === 'WMA'){
            $file->new = implode('.', $explode) . '.mp3';
        }
        elseif(strtoupper($extension) === 'WAV'){
            $file->new = implode('.', $explode) . '.mp3';
        }
        if($file->new !== false){
            $file->new = str_replace(['\'', '"'], '', $file->new);
            if(File::exist($file->new)){
                continue;
            }
            File::move($file->url, $file->url . '.temp');
            $command = 'ffmpeg -i "' .
                str_replace(['(', ')', '"'], ['(', ')', '\\"'], $file->url) . '.temp' .
                '" -vn -ar 44100 -ac 2 -ab 320k -f mp3 "' .
                str_replace(['(', ')', '\''], ['(', ')', '\\"'], $file->new) .
                '\'';
            echo $command . PHP_EOL;
            exec($command);
            File::move($file->url . '.temp', $file->url);
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


