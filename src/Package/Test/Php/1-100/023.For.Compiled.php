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
use Raxon\Module\Data;
use Raxon\Parse\Module\Parse;

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
    $data = new Data($app->data());
    $content = [];
    for(;
        $this->value_smaller_equal(
            $data->get('i'),
            (
                $this->value_plus($data->get('read_line'),3)
            )
        );
        $data->set('i', $this->value_plus_plus($data->data('i'))
    )){
        $content[] =  "yes";
    }
    ddd($content);
} catch (Exception | LocateException | ObjectException $exception) {
    echo $exception;
}