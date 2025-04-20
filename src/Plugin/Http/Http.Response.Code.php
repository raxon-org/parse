<?php
namespace Plugin;

use Raxon\Module\Handler;

trait Http_Response_Code {

    protected function http_response_code(int $code=200): void
    {
        $string = 'Status: ' . $code;
        Handler::header($string, $code, true);
    }
}