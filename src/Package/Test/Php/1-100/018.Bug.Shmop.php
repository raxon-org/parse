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
use Raxon\Module\SharedMemory;
use Raxon\Module\Time;

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

    /*
    $chars = chars();
    $count = count($chars);
    $words = [];
    for($i = 0; $i < 100000000; $i++){
        $words[] = random_word($chars, $count);
    }
    $write = implode(' ', $words);
    */
    $url = '/mnt/Disk2/Test/data.txt';
//    $size = File::write($url, $write);
//    ddd(File::size_format($size));
    $start = microtime(true);
    $size = File::size($url);
    $read = File::read($url);
    $duration_read = microtime(true) - $start;
    echo 'File read time: ' . Time::format($duration_read, '') . PHP_EOL;
    $part_size = (1024 * 1024) * 4;
    $parts = ceil($size / $part_size);
    $split = mb_str_split($read, $part_size);
    $offset = 100;
    $start= microtime(true);
    for($i = 0; $i < $parts; $i++){
        $shmop = SharedMemory::open($offset + $i, 'n', 0600, $part_size);
        $memory_data = $split[$i] . "\0";
        if($shmop){
            SharedMemory::write($shmop, $memory_data);
        }
        $duration_write = microtime(true) - $start;
        echo 'Memory write time: ' . Time::format($duration_write, '') . ' ' . File::size_format($part_size / $duration_write) . '/sec' . PHP_EOL;
    }


    $duration = microtime(true) - $app->config('time.start');
    echo Time::format($duration,'') . PHP_EOL;
//    ddd($words);
} catch (Exception | LocateException | ObjectException $exception) {
    echo $exception;
}

function random_word($chars, $count){
    $wordlength = rand(2, 25);
    $word = '';
    for($i=0; $i < $wordlength; $i++){
        $letter = rand(0, $count - 1);
        $word .= $chars[$letter];
    }
    return $word;
}

function chars(): array
{
    $chars = [];
    $chars[] = 'a';
    $chars[] = 'b';
    $chars[] = 'c';
    $chars[] = 'd';
    $chars[] = 'e';
    $chars[] = 'f';
    $chars[] = 'g';
    $chars[] = 'h';
    $chars[] = 'i';
    $chars[] = 'j';
    $chars[] = 'k';
    $chars[] = 'l';
    $chars[] = 'm';
    $chars[] = 'n';
    $chars[] = 'o';
    $chars[] = 'p';
    $chars[] = 'q';
    $chars[] = 'r';
    $chars[] = 's';
    $chars[] = 't';
    $chars[] = 'u';
    $chars[] = 'v';
    $chars[] = 'w';
    $chars[] = 'x';
    $chars[] = 'y';
    $chars[] = 'z';

    foreach($chars as $char){
        $chars[] = strtoupper($char);
    }
    return $chars;
}