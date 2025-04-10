<?php

use Raxon\Exception\ObjectException;

use Raxon\Module\Core;

trait Capture_append {

    /**
     * @throws ObjectException
     */
    public function capture_append(string $content, string $name): void
    {
        $data = $this->data();
        $list = $data->get($name);
        if(!is_array($list)){
            $list = [];
        }
        if(empty($list)){
            $list = [];
        }
        $list[] = $content;
        $data->set($name, $list);
    }
}