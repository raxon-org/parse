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

    $chars = chars();
    $count = count($chars);

    $words = [];
    for($i = 0; $i < 1000; $i++){
        $words[] = random_word($chars, $count);
    }
    $duration = microtime(true) - $app->config('time.start');
    Time::format($duration,'');
    ddd($words);
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