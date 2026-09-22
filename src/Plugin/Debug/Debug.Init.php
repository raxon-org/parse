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

trait Debug_Exception_Read {
    /**
     * @throws ObjectException
     */
    function debug_init(): string
    {
        $object = $this->object();
        $url = $object->config('controller.dir.data') . '/' .
            'Debug' . $object->config('extension.json');
        $data = $object->data_read($url);
        ddd($data);
        return '{}';
        /*
        if($data !== false){
            $result =  $data->get('Debug.Exception');
            $result = Core::object($result, Core::JSON_LINE);
            dd($result);
            return $result;
        }
        return '[]';
        */
    }

}