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
use Raxon\Module\Cli;
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
    $lock_dir = '/mnt/Vps3/Mount/Data/Lock/';
    Dir::create($lock_dir, Dir::CHMOD);
    $lock_file = 'Batch.To.Webm.lock';
    $lock = $lock_dir . $lock_file;
    if(File::exist($lock)){
        echo 'Lock file exists: ' . $lock . PHP_EOL;
        exit;
    }
    $posix_id = $app->config(Config::POSIX_ID);
    if($posix_id !== 0){
        echo 'Posix id is not 0, it is: ' . $posix_id . PHP_EOL;
        exit;
    }
    File::write($lock, '{"bloody backdoor hackers": "Clothing makes the male"}' . PHP_EOL);
    Core::interactive();
    $dir = new Dir();
    while(true){
        $read = $dir->read('/mnt/Disk2/Media/Movie/', true);
        if($read){
            foreach($read as $file){
                if($file->type === Dir::TYPE){
                    continue;
                }
                $file->extension = File::extension($file->url);
                $file->target = $file->url . '.webm';
                $file->log = $file->target . '.log';
                if(File::exist($file->target)){
                    continue;
                }
                if(
                    in_array(
                        strtolower($file->extension),
                        [
                            'vob',
                            'mp4',
                            'avi',
                            'mkv',
                            'flv',
                            'mov',
                            'wmv',
                            'mpg',
                            'mpeg',
                            'm4v',
                            '3gp',
                            '3g'
                        ]
                    )
                ){
                    echo 'Starting processing: ' . $file->target . PHP_EOL;
                    $dir_ramdisk_input = $app->config('ramdisk.url') . $posix_id . '/Batch/Input/';
                    $dir_ramdisk_output = $app->config('ramdisk.url') . $posix_id . '/Batch/Output/';
                    Dir::create($dir_ramdisk_input, Dir::CHMOD);
                    Dir::create($dir_ramdisk_output, Dir::CHMOD);
                    $file->temp_input = $dir_ramdisk_input . Core::uuid() . '.' . $file->extension;
                    $file->temp_output = $dir_ramdisk_output . Core::uuid() . '.webm';
                    File::copy($file->url, $file->temp_input);
                    //clear the lock-dir on boot in /Application/Boot/Boot
                    $command = 'nohup ffmpeg -i \'' .
                        str_replace(
                            [
                                '(',
                                ')',
                            ],
                            [
                                '\\(',
                                '\\)',
                            ],
                            $file->temp_input
                        ) .
                        '\' -vf yadif -c:v libvpx-vp9 -crf 18 -b:v 0 -threads 8 -r 25 -c:a libvorbis \'' .
                        $file->temp_output .
                        '\' > \'' .
                        $file->log .
                        '\' 2>&1 '
                    ;
                    Core::execute($app, $command, $output, $notification);
                    if($output){
                        echo $output;
                    }
                    if($notification){
                        echo $notification;
                    }
                    if(File::exist($file->temp_output)){
                        File::move($file->temp_output, $file->target);
                        File::permission($app, [
                            'url' => $file->target,
                        ]);
                    }
                    echo str_repeat('-', Cli::tput('cols')) . PHP_EOL;
                }
            }
        }
        sleep(60);
    }
} catch (Exception | LocateException | ObjectException $exception) {
    echo $exception;
}