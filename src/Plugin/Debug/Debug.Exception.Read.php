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
    function debug_exception_read(mixed $return_type='array'): mixed
    {
        $object = $this->object();
        $url = $object->config('controller.dir.data') . '/' .
            'Debug.Exception' . $object->config('extension2.json');
        $data = $object->data_read($url);
        if($data !== false){
            $result =  $data->get('Debug.Exception');
            $result = Core::object($result, $return_type);
            ddd($result);
            return $result;
        }
        return [];
    }

}