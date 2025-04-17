<?php
namespace Plugin;

use Raxon\Parse\Attribute\Argument;

trait Capture_Prepend {

    #[Argument(apply: "literal", count: 1, index:1)]
    public function capture_prepend(string $content, string $name): void
    {
        $name = trim($name, '\'"');
        if(substr($name, 0, 1) === '$'){
            $name = substr($name, 1);
        }
        $data = $this->data();
        $list = $data->get($name);
        if(!is_array($list)){
            $list = [];
        }
        if(empty($list)){
            $list = [];
        }
        array_unshift($list, $content);
        $data->set($name, $list);
    }
}