<?php
namespace Plugin;

trait Script {

    public function script(mixed $script, $name='script'): void
    {
        $value = [];
        $data = $this->data();
        switch($name){
            case 'ready':
                $value[] = '<script type="text/javascript">';
                $value[] = 'ready(() => {';
                $value[] = $script;
                $value[] = '});';
                $value[] = '</script>';
            break;
            case 'module':
                $value[] = '<script type="module">';
                $value[] = $script;
                $value[] = '</script>';
            break;
            case 'script':
                $value[] = '<script type="text/javascript">';
                $value[] = $script;
                $value[] = '</script>';
            break;
        }
        $name = 'script';
        $list = $data->get($name);
        if(!is_array($list)){
            $list = [];
        }
        if(empty($list)){
            $list = [];
        }
        $value = implode(PHP_EOL, $value);        
        $list[] = $value;
        d($name);
        d($data->get($name));
        $data->set($name, $list);
    }
}