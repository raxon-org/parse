<?php
namespace Plugin;

use Raxon\Exception\FileWriteException;
use Raxon\Exception\ObjectException;
use Raxon\Module\Core;

trait Data_Select {

    /**
     * @throws FileWriteException
     * @throws ObjectException
     */
    protected function data_select(string $url, string $select=null): mixed
    {
        $parse = $this->parse();
        $data = $this->data();
        return Core::object_select($parse, $data, $url, $select, false);
    }
}