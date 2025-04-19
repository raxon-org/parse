<?php
namespace Plugin;

use Exception;
use Raxon\Exception\UrlEmptyException;
use Raxon\Module\Core;

trait Redirect {

    protected function redirect(string $url): ?string
    {
        try {
            Core::redirect($url);
        } catch(Exception | UrlEmptyException $exception){
            return $exception->getMessage();
        }
        return null;
    }

}