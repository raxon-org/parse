<?php
namespace Plugin;
/**
 * @package Plugin
 * @author Remco van der Velde
 * @since 2025-02-15
 * @license MIT
 * @version 1.0
 * @changeLog
 *    - all
 */

use Raxon\Module\Core;
use Raxon\Module\File;

use Raxon\Exception\ObjectException;

use Exception;

trait Parse_Read {

    /**
     * @throws Exception
     */
    protected function parse_read(string $url, bool $cache=true): mixed
    {
        if(File::exist($url)){
            $object = $this->object();
            if($cache){
                $read = $object->compile_read($url, sha1($url));
            } else {
                $read = $object->compile_read($url);
            }
            if($read){
                try {
                    $script = $object->data('script') ?? [];
                    $script_merge = $read->data('script') ?? [];
                    if(array_key_exists(0, $script_merge)){
                        $script = array_merge($script, $script_merge);
                        $object->data('script', $script);
                    }
                    $link = $object->data('link') ?? [];
                    $link_merge = $read->data('link') ?? [];
                    if(array_key_exists(0, $link_merge)){
                        $link = array_merge($link, $link_merge);
                        $object->data('link', $link);
                    }
                } catch (ObjectException $e) {
                }
                return $read->data();
            }
        } else {
            throw new Exception('Error: url=' . $url . ' not found');
        }
        return '';
    }
}