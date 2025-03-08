<?php

namespace Plugin;

use Exception;

use Raxon\Module\Core;

trait Execute
{

    /**
     * @throws Exception
     */
    protected function execute(string $command='', $notification=''): ?string
    {
        $object = $this->object();
        $data = $this->storage();
        $command = escapeshellcmd($command);
        $output = false;
        Core::execute($object, $command, $output, $notify);
        if($notification){
            if(
                is_string($notification) &&
                substr($notification, 0, 1) === '$'
            ){
                $notification = substr($notification, 1);
            }
            $data->data($notification, $notify);
        }
//    exec($command, $output);
        return $output;
    }
}