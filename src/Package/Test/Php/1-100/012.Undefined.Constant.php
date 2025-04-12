<?php
set_error_handler(function ($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

try {
    echo UNDEFINED_CONSTANT;
} catch (Error | ErrorException | Exception $e) {
    echo "Caught warning: " . $e->getMessage();
}