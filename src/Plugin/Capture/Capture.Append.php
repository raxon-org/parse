<?php
namespace Plugin;

trait Capture_append {

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