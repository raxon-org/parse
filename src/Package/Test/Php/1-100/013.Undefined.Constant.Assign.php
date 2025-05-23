<?php
namespace Package\Raxon\Parse;
use Error;
use ErrorException;
use Exception;
set_error_handler(function ($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

class Temp {
    public function __construct()
    {
        echo 'Temp class instantiated' . PHP_EOL;
    }

    public function run()
    {
        try {
            $test = UNDEFINED_CONSTANT;
            echo $test;
        } catch (Error|Exception $e) {
            echo "Caught warning: " . $e->getMessage();
        }
    }

}

$obj = new Temp();
$obj->run();



