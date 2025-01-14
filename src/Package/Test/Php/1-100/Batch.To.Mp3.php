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
use Raxon\Exception\FileMoveException;

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

    batch([
        '/mnt/Disk2/Media/Music/',
        '/mnt/Disk2/Media/Voice/',
    ]);
} catch (Exception | LocateException | ObjectException $exception) {
    echo $exception;
}

/**
 * @throws FileMoveException
 */
function batch($list=[]): void
{
    while(true){
        $dir = new Dir();
        foreach($list as $nr => $url){
            $read = $dir->read($url, true);
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
                        '"';
                    echo $command . PHP_EOL;
                    exec($command);
                    File::move($file->url . '.temp', $file->url);
                }
            }
        }
        sleep(5);
    }

}