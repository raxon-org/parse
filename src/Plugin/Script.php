<?php
namespace Plugin;

use Raxon\Exception\ObjectException;

use Raxon\Module\Core;

trait Script {

    /**
     * @throws ObjectException
     */
    public function script($name='script', mixed $script=null): mixed
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
            return null;
        } else {
            $block = $name;
            $name = $script;
            $value = [];
            switch($name){
                case 'ready':
                    $value[] = '<script type="text/javascript">';
                    $value[] = 'ready(() => {';
                    $value[] = $block;
                    $value[] = '});';
                    $value[] = '</script>';
                break;
                case 'module':
                    $value[] = '<script type="module">';
                    $value[] = $block;
                    $value[] = '</script>';
                break;
                case 'script':
                    $value[] = '<script type="text/javascript">';
                    $value[] = $block;
                    $value[] = '</script>';
                break;
            }
            $list = $this->data($name);
            if(!is_array($list)){
                $list = [];
            }
            if(empty($list)){
                $list = [];
            }
            $value = implode(PHP_EOL, $value);
            $list[] = $value;
            $this->data($name, $list);
            return null;
        }
    }
}