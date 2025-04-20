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

use Exception;
use Raxon\Module\Core;

trait Date
{

    /**
     * @throws Exception
     */
    protected function date(string $format, int $timestamp=null): ?string
    {
        if(empty($format)){
            $format = 'Y-m-d H:i:s';
        }
        elseif($format === true){
            $format = 'Y-m-d H:i:s P';
        }
        elseif(defined($format)){
            $format = constant($format);
        }
        if($timestamp === null){
            $timestamp = time();
        }
        return date($format, $timestamp);
    }
}