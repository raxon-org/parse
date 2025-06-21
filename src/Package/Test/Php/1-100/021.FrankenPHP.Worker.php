<?php
/**
 * @author          Remco van der Velde
 * @since           2025-06-21
 * @version         1.0
 * @changeLog
 *     -    all
 */

use Raxon\App;
use Raxon\Config;

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

    $handler = static function () use ($app) {
        // Called when a request is received,
        // superglobals, php://input and the like are reset
        echo App::run($app);
//        var_dump($_GET);
//        var_dump($_POST);
//        var_dump($_COOKIE);
//        var_dump($_FILES);
//        var_dump($_SERVER);
//        var_dump($app->request());
    };
    $count = 0;
    $max = 5;
    while (frankenphp_handle_request($handler)) {
        $count++;
        if($count >= $max){
            break;
        }
    }
} catch (Exception | LocateException | ObjectException $exception) {
    echo $exception;
}