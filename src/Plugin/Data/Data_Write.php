<?php
namespace Plugin;

use Exception;
use Raxon\Exception\ObjectException;
use Raxon\Module\Core;
use Raxon\Module\File;

trait Data_Write {

    /**
     * @throws ObjectException
     * @throws Exception
     */
    protected function data_write(string $url, mixed $data=null, array $options=[]): void
    {
        $options['output'] = $options['output'] ?? Core::JSON;
        $data = Core::object($data, $options['output']);
        $bytes = File::write($url, $data);
    }
}