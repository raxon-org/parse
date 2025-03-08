<?php
namespace Plugin;

use Raxon\Module\Core;

trait Script {

    public function script($name='script', $script=null): mixed
    {
        $object = $this->object();
        $data = $this->data();
        ddd($data);
        if(is_array($script) || is_object($script)){
            return Core::object($script, Core::JSON);
        } else {
            $script = trim($script);
        }
        if($name === 'ready'){
            $name = 'script';
            $value = [];
            $value[] = '<script type="text/javascript">';
            $value[] = 'ready(() => {';
            $value[] = $script;
            $value[] = '});';
            $value[] = "\t\t\t" . '</script>';
        }
        elseif($name === 'module'){
            $name = 'script';
            $value = [];
            $value[] = '<script type="module">';
            $value[] = $script;
            $value[] = "\t\t\t" . '</script>';
        } else {
            $value = [];
            $value[] = '<script type="text/javascript">';
            $value[] = $script;
            $value[] = "\t\t\t" . '</script>';
        }
        $list = $data->data($name);
        if(is_string($list)){
            $list = [];
        }
        if(empty($list)){
            $list = [];
        }
        $value = implode("\n", $value);
        $list[] = $value;
        $data->data($name, $list);
        d($name);
        d($list);
        ddd($data);
        return null;
    }

}