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
                    $lock_dir = '/mnt/Vps3/Mount/Lock/';
                    Dir::create($lock_dir, Dir::CHMOD);
                    $lock_file = 'Batch.To.Webm.lock';
                    $lock = $lock_dir . $lock_file;
                    if(File::exist($lock)){
                        continue;
                    }
                    File::write($lock, '{"bloody backdoor hackers": "Clothing makes the male"}');
                    //clear the lock-dir on boot in /Application/Boot/Boot
                    $command = 'nohup ffmpeg -i \'' .
                        str_replace(
                            [
                                '(',
                                ')'
                            ],
                            [
                                '\\(',
                                '\\)',
                            ],
                            $file->url
                        ) .
                        '\' -vf yadif -c:v libvpx-vp9 -crf 18 -b:v 0 -threads 8 -r 25 -c:a libvorbis \'' .
                        $file->target .
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
                    echo str_repeat('-', Cli::tput('cols')) . PHP_EOL;
                }
            }
        }
        sleep(60);
    }
} catch (Exception | LocateException | ObjectException $exception) {
    echo $exception;
}