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
    $date = date('Y-m-d');
    $dir_log = '/mnt/Disk2/Log/Docker/';
    $dir_archive = '/mnt/Disk2/Log/Docker/Archive/';
    $dir_date = '/mnt/Disk2/Log/Docker/Archive/' . $date . '/';
    Dir::create($dir_log, Dir::CHMOD);
    Dir::create($dir_archive, Dir::CHMOD);
    Dir::create($dir_date, Dir::CHMOD);
    $url_docker = $dir_log . 'Docker.log';
    $url_docker_archive = $dir_date . date('His') . '.' . 'Docker.log';
    $url_docker_output = $dir_log . 'Docker.output.log';
    $url_docker_output_archive = $dir_date . date('His') . 'Docker.output.log';
    $url_docker_notification = $dir_log . 'Docker.notification.log';
    $url_docker_notification_archive = $dir_date . date('His') . 'Docker.notification.log';
    if(File::exist($url_docker)){
        File::move($url_docker, $url_docker_archive);
        File::delete($url_docker);
    }
    if(File::exist($url_docker_output)){
        File::move($url_docker_output, $url_docker_output_archive);
        File::delete($url_docker_output);
    }
    if(File::exist($url_docker_notification)){
        File::move($url_docker_notification, $url_docker_notification_archive);
        File::delete($url_docker_notification);
    }
    $command = 'docker stats --no-stream --no-trunc > ' . $url_docker;
    while(true){
        $date_new = date('Y-m-d');
        if($date_new !== $date){
            $time = microtime(true);
            $date = $date_new;
            $url_docker_archive = $dir_date . $time . '.' . 'Docker.log';
            $url_docker_output = $dir_log . 'Docker.output.log';
            $url_docker_output_archive = $dir_date . $time . 'Docker.output.log';
            $url_docker_notification = $dir_log . 'Docker.notification.log';
            $url_docker_notification_archive = $dir_date . $time . 'Docker.notification.log';
            if(File::exist($url_docker)){
                File::move($url_docker, $url_docker_archive);
                File::delete($url_docker);
            }
            if(File::exist($url_docker_output)){
                File::move($url_docker_output, $url_docker_output_archive);
                File::delete($url_docker_output);
            }
            if(File::exist($url_docker_notification)){
                File::move($url_docker_notification, $url_docker_notification_archive);
                File::delete($url_docker_notification);
            }
        }
        Core::execute($app, $command, $output, $notification);
        $command_output = File::append($url_docker_output, $output);
        $command_notification = File::append($url_docker_notification, $notification);
        sleep(10);
        File::permission($app, [
            'docker' => '/mnt/Disk2/Log/Docker.log',
            'docker_archive' => '/mnt/Disk2/Log/Docker/Archive/',
            'docker_output' => '/mnt/Disk2/Log/Docker.output.log',
            'docker_output_archive' => '/mnt/Disk2/Log/Docker/Archive/',
            'docker_notification' => '/mnt/Disk2/Log/Docker.notification.log',
            'docker_notification_archive' => '/mnt/Disk2/Log/Docker/Archive/',
            'dir' => '/mnt/Disk2/Log/',
        ]);
    }
} catch (Exception | LocateException | ObjectException $exception) {
    echo $exception;
}