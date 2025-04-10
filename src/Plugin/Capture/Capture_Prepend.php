<?php
namespace Plugin;

trait Capture_Prepend {

    public function capture_prepend(string $content, string $name): void
    {
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