<?php
namespace Plugin;
/**
 * @package Plugin
 * @author Remco van der Velde
 * @since 2025-02-22
 * @license MIT
 * @version 1.0
 * @changeLog
 *    - all
 */

use Exception;
use Raxon\Module\File;

trait File_Put {

    public function file_put(string $url=null, string $content='', array $options=[]): bool | int
    {
        try {
            $options['flags'] = $options['flags'] ?? LOCK_EX;
            if(is_string($options['flags'])){
                $options['flags'] = constant($options['flags']);
            }
            $options['return'] = $options['return'] ?? 'size';
            return File::put($url, $content, $options);
        } catch (Exception $e){
            return false;
        }
    }

}