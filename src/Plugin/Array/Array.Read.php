<?php
namespace Plugin;

use Exception;

use Raxon\Exception\ObjectException;
use Raxon\Module\Core;
use Raxon\Module\File;

trait Array_Read {

    /**
     * @throws ObjectException
     */
    protected function array_read(string $url=''): array
    {
        if(File::exist($url)){
            $read = File::read($url);
            return Core::object($read, Core::OBJECT_ARRAY);
        } else {
            throw new Exception('Error: url:' . $url . ' not found');
        }
    }
}