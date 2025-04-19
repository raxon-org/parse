<?php
namespace Plugin;

use Exception;
use Raxon\Module\Handler;

trait Request_Method {

    /**
     * @throws Exception
     */
    protected function request_method(): string
    {
        return Handler::method();
    }
}