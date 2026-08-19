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
use Raxon\Module\Core;
use Raxon\Module\Data;
use Raxon\Parse\Module\Parse;

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
    $dir = new Dir();
    $url = '/mnt/d';
    $read = $dir->read($url, true);
    $list = [];
    if($read){
        foreach($read as $file){
            if($file->type === Dir::TYPE){
                continue;
            }
            elseif($file->type === File::TYPE){
                $file->extension = File::extension($file->url);
                if(in_array($file->extension, ['zip'], true)){
                    $file->size = File::size($file->url);
                    $list[] = $file->url;
                }
            }
        }
        $url = '/mnt/d/list.json';
        File::write($url, Core::object($list, Core::JSON));
    }


} catch (Exception | LocateException | ObjectException $exception) {
    echo $exception;
}