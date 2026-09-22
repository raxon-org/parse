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

trait Debug_Exception {
    function debug_exception(mixed $value): array
    {
        $object = $this->object();
        $url = $object->config('controller.dir.data') . '/' .
            'Debug.Exception' . $object->config('extension.json');
        ddd($url);
        return [];
    }

}