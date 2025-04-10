<?php
namespace Plugin;

trait Script {

    public function script($name='script', mixed $script=null): void
    {
        if(
            // old parser
            in_array(
                $name,
                [
                    'script',
                    'ready',
                    'module'
                ],
                true
            )
        ){
            $object = $this->object();
            $data = $this->storage();
            if(is_array($script) || is_object($script)){
                if(is_object($script)){
                    $parents = class_parents($script);
                    if(in_array('Exception', $parents)){
                        //this happened rebuilding the parser with trait plugins instead of functions
                        throw $script;
                    }
                }
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
        } else {
            $content = $name;
            $name = $script;
            $value = [];
            $data = $this->data();
            switch($name){
                case 'ready':
                    $value[] = '<script type="text/javascript">';
                    $value[] = 'ready(() => {';
                    $value[] = $content;
                    $value[] = '});';
                    $value[] = '</script>';
                break;
                case 'module':
                    $value[] = '<script type="module">';
                    $value[] = $content;
                    $value[] = '</script>';
                break;
                case 'script':
                    $value[] = '<script type="text/javascript">';
                    $value[] = $content;
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
            $data->set($name, $list);
        }
    }
}