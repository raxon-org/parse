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
    $dir_log = '/mnt/Disk2/Log/';
    $dir_log_docker = '/mnt/Disk2/Log/Docker/';
    $dir_archive = $dir_log_docker . 'Archive/';
    $dir_live = $dir_log_docker . 'Live/';
    $dir_date = $dir_archive .  $date . '/';
    $time = microtime(true);
    Dir::create($dir_log_docker, Dir::CHMOD);
    Dir::create($dir_live, Dir::CHMOD);
    Dir::create($dir_archive, Dir::CHMOD);
    Dir::create($dir_date, Dir::CHMOD);
    $url_docker = $dir_log . 'Docker.log';
    $url_docker_live = $dir_live . 'Docker.log';
    $url_docker_archive = $dir_date . $time . '.' . 'Docker.log';
    $url_docker_output = $dir_log . 'Docker.output.log';
    $url_docker_output_live = $dir_live . 'Docker.output.log';
    $url_docker_output_archive = $dir_date . $time . '.' . 'Docker.output.log';
    $url_docker_notification = $dir_log . 'Docker.notification.log';
    $url_docker_notification_live = $dir_live . 'Docker.notification.log';
    $url_docker_notification_archive = $dir_date . $time . '.' . 'Docker.notification.log';
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
            $dir_date = $dir_archive .  $date . '/';
            $url_docker_archive = $dir_date . $time . '.' . 'Docker.log';
            $url_docker_output = $dir_log_docker . 'Docker.output.log';
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
            File::permission($app, [
                'url_docker_archive' => $url_docker_archive,
                'url_docker_output_archive' => $url_docker_output_archive,
                'url_docker_notification_archive' => $url_docker_notification_archive,
            ]);
        }
        Core::execute($app, $command, $output, $notification);
        $command_output = File::append($url_docker_output, $output);
        $command_notification = File::append($url_docker_notification, $notification);
        $bottom = explode(PHP_EOL, File::read($url_docker));
        $header = false;
        foreach($bottom as $nr => $line){
            if(substr($line, 0, 3) === 'CON'){
                $header = $line;
                unset($bottom[$nr]);
            }
        }
        $bottom = implode(PHP_EOL, $bottom);
        $top = explode(PHP_EOL, File::read($url_docker_live));
        foreach($top as $nr => $line){
            if(substr($line, 0, 3) === 'CON'){
                $header = $line;
                unset($top[$nr]);
            }
        }
        $top = implode(PHP_EOL, $top);
        breakpoint($header . $top . $bottom);
        File::write($url_docker_live, $top . $bottom);
        $bottom = explode(PHP_EOL, File::read($url_docker_output));
        foreach($bottom as $nr => $line){
            if(substr($line, 0, 3) === 'CON'){
                $header = $line;
                unset($bottom[$nr]);
            }
        }
        $bottom = implode(PHP_EOL, $bottom);
        $top = explode(PHP_EOL, File::read($url_docker_output_live));
        foreach($top as $nr => $line){
            if(substr($line, 0, 3) === 'CON'){
                $header = $line;
                unset($top[$nr]);
            }
        }
        $top = implode(PHP_EOL, $top);
        breakpoint($header . $top . $bottom);
        File::write($url_docker_live, $top . $bottom);
        $top = explode(PHP_EOL, File::read($url_docker_notification_live));
        foreach($top as $nr => $line){
            if(substr($line, 0, 3) === 'CON'){
                $header = $line;
                unset($top[$nr]);
            }
        }
        $top = implode(PHP_EOL, $top);
        $bottom = explode(PHP_EOL, File::read($url_docker_notification));
        foreach($bottom as $nr => $line){
            if(substr($line, 0, 3) === 'CON'){
                $header = $line;
                unset($bottom[$nr]);
            }
        }
        $bottom = implode(PHP_EOL, $bottom);
        breakpoint($header . $top . $bottom);
        File::write($url_docker_notification_live, $top . $bottom);
        File::permission($app, [
            'dir' => $dir,
            'dir_log' => $dir_log,
            'dir_log_docker' => $dir_log_docker,
            'dir_archive' => $dir_archive,
            'dir_date' => $dir_date,
            'dir_live' => $dir_live,
            'url_docker_live' => $url_docker_live,
            'url_docker_output_live' => $url_docker_output_live,
            'url_docker_output_archive' => $url_docker_output_archive,
            'url_docker_notification_live' => $url_docker_notification_live,
            'url_docker_notification_archive' => $url_docker_notification_archive,
        ]);
        sleep(5);
    }
} catch (Exception | LocateException | ObjectException $exception) {
    echo $exception;
}