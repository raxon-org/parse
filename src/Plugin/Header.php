<?php
/**
 * @package Plugin\Modifier
 * @author Remco van der Velde
 * @since 2024-08-19
 * @license MIT
 * @version 1.0
 * @changeLog
 *    - all
 */
namespace Plugin;

use Raxon\Module\Handler;

trait Header {

    public function header(string $string='', int|string $http_response_code=null, bool $replace=true): void
    {
        Handler::header($string, $http_response_code, $replace);
    }

}