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

trait Memory_Usage
{

    protected function memory_usage(string $format=''): ?string
    {
        $usage = memory_get_peak_usage(true);
        switch(strtoupper($format)){
            case 'B' :
                $result = $usage . ' B';
                break;
            case 'KB' :
                $result = round($usage / 1024, 2) . ' KB';
                break;
            case 'MB' :
                $result = round($usage / 1024 / 1024, 2) . ' MB';
                break;
            case 'GB' :
                $result = round($usage / 1024 / 1024 / 1024, 2) . ' GB';
                break;
            case 'TB' :
                $result = round($usage / 1024 / 1024 / 1024 / 1024, 2) . ' TB';
                break;
            case 'PB' :
                $result = round($usage / 1024 / 1024 / 1024 / 1024 / 1024, 2) . ' PB';
                break;
            default :
                $result = $usage;
                break;
        }
        return $result;
    }
}