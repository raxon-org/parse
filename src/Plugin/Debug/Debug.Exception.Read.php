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

trait Debug_Exception_Read {
    function debug_exception_read(mixed $return_type='array'): mixed
    {
        $object = $this->object();
        $url = $object->config('controller.dir.data') . '/' .
            'Debug.Exception' . $object->config('extension.json');
        ddd($url);
        return [];
    }

}