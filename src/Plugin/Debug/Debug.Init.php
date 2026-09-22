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

use Raxon\Exception\ObjectException;
use Raxon\Module\Core;

trait Debug_Init {
    /**
     * @throws ObjectException
     */
    function debug_init(): string
    {
        $object = $this->object();
        $url = $object->config('controller.dir.data') . '/' .
            'Debug' . $object->config('extension.json');
        $data = $object->data_read($url);
        if($data === false){
            return '{}';
        }
        $result =  $data->get('Debug');
        if($result === null){
            return '{}';
        }
        $result = Core::object($result, Core::JSON_LINE);
        return $result;
    }

}