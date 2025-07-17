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

    $array = [  
        (object) [
            'role' => 'user',
            'content' => "{{json.encode(file.read(\"/mnt/Vps3/Mount/Shared/Plugin/Host/Host.Domain.php\"))}}" 
        ]
    ];
    $json = json_encode($array);

    $options = (object) [
        'source' => 'internal_test'
    ];
    $data = new Data($app->data());    
    $parse = new Parse($$app, $data, App::flags($app), $options);
    $output = $parse->compile($json, $data);
    ddd($output);
} catch (Exception | LocateException | ObjectException $exception) {
    echo $exception;
}