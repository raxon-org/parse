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

if(!file_exists($dir_vendor)){
    $dir_vendor =
        DIRECTORY_SEPARATOR .
        'home' .
        DIRECTORY_SEPARATOR .
        'remco' .
        DIRECTORY_SEPARATOR .
        'vps' .
        DIRECTORY_SEPARATOR .
        'vps3' .
        DIRECTORY_SEPARATOR .
        'vendor' .
        DIRECTORY_SEPARATOR
    ;
}

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
    $dir_log = '/mnt/Disk2/Log/';
    Dir::create($dir_log, Dir::CHMOD);
    $url_docker = $dir_log . 'Docker.log';
    File::move($app, $url_docker, $url_docker . '.' . Core::uuid() . '.original');
    File::delete($app, $url_docker);
    $url_docker_output = $dir_log . 'Docker.output.log';
    File::move($app, $url_docker_output, $url_docker_output . '.' . Core::uuid() . '.original');
    File::delete($app, $url_docker_output);
    $url_docker_notification = $dir_log . 'Docker.notification.log';
    File::move($app, $url_docker_notification, $url_docker_notification . '.' . Core::uuid() . '.original');
    File::delete($app, $url_docker_notification);
    $command = 'docker stats --no-stream --no-trunc > ' . $url_docker;
    while(true){
        Core::execute($app, $command, $output, $notification);
        $command_output = File::append($url_docker_output, $output);
        $command_notification = File::append($url_docker_notification, $notification);
        sleep(10);
        File::permission($app, [
            'docker' => '/mnt/Disk2/Log/Docker.log',
            'docker_output' => '/mnt/Disk2/Log/Docker.output.log',
            'docker_notification' => '/mnt/Disk2/Log/Docker.notification.log',
            'dir' => '/mnt/Disk2/Log/',
        ]);
    }
} catch (Exception | LocateException | ObjectException $exception) {
    echo $exception;
}