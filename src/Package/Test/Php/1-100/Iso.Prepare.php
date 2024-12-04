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
use Raxon\Module\Data;
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
    $read = $dir->read('/mnt/Vps3/', true);
    $target_dir = '/mnt/Disk2/Media/Backup/Vps-2024-12-04/';
    Dir::create($target_dir, Dir::CHMOD);
    $target_prefix = 'Vps3-data-';
    $tree = 'tree' . $app->config('extension.json');
    $target_tree = $target_dir . $tree;
    $data = new Data();
    $data->set('Summary.time', microtime(true));
    $size_total = 0;
    $size_batch = 0;
    $size_per_directory = 4 * 1024 * 1024 * 1024 ;
    $dir_number = 1;
    $file = (object) [
        'url' => false
    ];
    Dir::create($target_dir . $dir_number . '/', Dir::CHMOD);
    File::permission($app, [
        'dir' => $target_dir,
        'tree' => $target_tree,
        'number' => $target_dir . $dir_number . '/',
    ]);
    foreach($read as $nr => $file){
        $file->size = File::size($file->url);
        if(empty($file->size)){
            continue;
        }
        if($file->size > (2 * 1024 * 1024 * 1024)){
            continue;
        }
        if(($size_batch + $file->size) >= $size_per_directory){
            File::delete($target_dir . $dir_number . '.iso');
            $command = 'genisoimage -R -J -o '  . $target_dir . $dir_number . '.iso ' . $target_dir . $dir_number . '/';
            exec($command, $output);
            echo implode(PHP_EOL, $output) . PHP_EOL;
            $command = 'split -b 1024m ' . $target_dir . $dir_number . '.iso ' . $target_dir . $dir_number . '_';
            exec($command, $output);
            echo implode(PHP_EOL, $output) . PHP_EOL;
            File::delete($target_dir . $dir_number . '.iso');
            $read = $dir->read($target_dir);
            foreach($read as $nr => $file){
                if($file->type === File::TYPE) {
                    $extension = File::extension($file->url);
                    if($extension === ''){
                        $extension = 'iso';
                        File::move($file->url, $file->url . '.' . $extension);
                    }
                }
            }
            $dir_number++;
            Dir::create($target_dir . $dir_number . '/', Dir::CHMOD);
            $size_batch = 0;
        }
        if(!empty($file->size)){
            $size_total += $file->size;
            $size_batch += $file->size;
            $file->size_format = File::size_format($file->size);
            $file->uuid = Core::uuid();
            $file->dir_number = $dir_number;
            $file->extension = File::extension($file->url);
            $read_gz = gzencode(File::read($file->url), 9);
            $target_gz = $target_dir . $dir_number . '/' . $file->uuid . $app->config('extension.gzip');
            File::write($target_gz, $read_gz);
            $file->url = $target_gz;
            $data->set('Tree.' . $nr, $file);
            File::permission($app, [
                '1' => $target_dir . $dir_number . '/',
                '.iso' => $target_dir . $dir_number . '.iso',
            ]);
        }

    }
    $size_format = File::size_format($size_total);
    $data->set('Summary.size', $size_total);
    $data->set('Summary.size_format', $size_format);
    $data->set('Summary.duration', microtime(true) - $data->get('Summary.time'));
    $data->write($target_tree);
    File::delete($target_dir . $dir_number . '.iso');
    $command = 'genisoimage -R -J -split-output -o '  . $target_dir . $dir_number . '.iso ' . $target_dir . $dir_number . '/';
    exec($command, $output);
    echo implode(PHP_EOL, $output) . PHP_EOL;
    $command = 'split -b 1024m ' . $target_dir . $dir_number . '.iso ' . $target_dir . $dir_number . '_';
    exec($command, $output);
    echo implode(PHP_EOL, $output) . PHP_EOL;
    $dir_number++;
    Dir::create($target_dir . $dir_number . '/', Dir::CHMOD);
    $size_batch = 0;
    echo implode(PHP_EOL, $output) . PHP_EOL;
    $read = $dir->read($target_dir);
    foreach($read as $nr => $file){
        if($file->type === File::TYPE) {
            $extension = File::extension($file->url);
            if($extension === ''){
                $extension = 'iso';
                File::move($file->url, $file->url . '.' . $extension);
            }
        }
    }
} catch (Exception | LocateException | ObjectException $exception) {
    echo $exception;
}


