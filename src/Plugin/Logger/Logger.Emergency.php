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

trait Logger_Emergency {

    protected function logger_debug(string $message, array $context=[], string $channel=''): void
    {
        $object = $this->object();
        if(empty($channel)){
            $channel = $object->config('project.log.app');
        }
        if($channel){
            $object->logger($channel)->emergency($message, $context);
        }
    }
}