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
    d(App::options($app));
    echo 'node';
    breakpoint(App::flags($app));
} catch (Exception|LocateException|ObjectException $exception) {
    echo $exception;
}

function d($data=null, $options=[]): void
{
    if(!array_key_exists('trace', $options)){
        $options['trace'] = true;
    }
    $trace = debug_backtrace(1);
    if(ob_get_level() > 0){
        ob_end_flush();
    }
    if(!defined('IS_CLI')){
        echo '<pre class="priya-debug">' . PHP_EOL;
    }
    if(
        array_key_exists('trace', $options) &&
        $options['trace'] === true
    ){
        echo $trace[0]['file'] . ':' . $trace[0]['line'] . PHP_EOL;
    }
    elseif(
        array_key_exists('trace', $options)
    ){
        echo $options['trace'];
    }
    var_dump($data);
    if(!defined('IS_CLI')){
        echo '</pre>' . PHP_EOL;
    }
    flush();
}

 function breakpoint($data=null, $options=[]): void
{
    if(!array_key_exists('trace', $options)){
        $options['trace'] = true;
    }
    $trace = debug_backtrace(1);
    if(ob_get_level() > 0){
        ob_end_flush();
    }
    if(defined('IS_CLI')){
        ob_start();
        var_dump($data);
        $export = ob_get_clean();
        try {
            if(
                array_key_exists('trace', $options) &&
                $options['trace'] === true
            ){
                read_input($trace[0]['file'] . ':' . $trace[0]['line'] . PHP_EOL . $export . PHP_EOL . 'Press enter to continue or ctrl-c to break...');
            }
            elseif(
                array_key_exists('trace', $options) &&
                is_string($options['trace'])
            ){
                read_input($trace[0]['file'] . ':' . $trace[0]['line'] . PHP_EOL . $export . PHP_EOL . 'Press enter to continue or ctrl-c to break...');
            } else {
                read_input($trace[0]['file'] . ':' . $trace[0]['line'] . PHP_EOL . $export . PHP_EOL . 'Press enter to continue or ctrl-c to break...');
            }
        }
        catch(Exception | ObjectException $exception){
            echo (string) $exception;
        }
    }
}

function read_input($text=''){
    fwrite(STDOUT, $text);
    fflush(STDOUT);
    system('stty -echo');
//                $input = readline();
    $input = trim(fgets(STDIN));
    system('stty echo');
    fwrite(STDOUT, PHP_EOL);
}