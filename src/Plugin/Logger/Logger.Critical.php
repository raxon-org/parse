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

trait Logger_Critical {

    protected function logger_critical(string $message, array $context=[], string $channel=''): void
    {
        $object = $this->object();
        if(empty($channel)){
            $channel = $object->config('project.log.error');
        }
        if($channel){
            $object->logger($channel)->critical($message, $context);
        }
    }
}