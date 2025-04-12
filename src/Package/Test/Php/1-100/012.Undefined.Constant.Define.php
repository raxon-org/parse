<?php
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
            echo UNDEFINED_CONSTANT;
        } catch (Error|ErrorException|Exception $e) {
            echo "Caught warning: " . $e->getMessage();
        }
    }

}

$obj = new Temp();
$obj->run();



