<?php
namespace Plugin;

use Raxon\Exception\ObjectException;

use Raxon\Module\Core;

trait Link {

    /**
     * @throws ObjectException
     */
    public function link(string $content): void
    {
        $data = $this->data();
        $name = 'link';
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