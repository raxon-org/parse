<?php
namespace Plugin;

use Raxon\Parse\Attribute\Argument;

use Package\Raxon\Markdown\Module as Module;

trait Block_Markdown {

    #[Argument(apply: "literal", count: 1, index:1)]
    protected function block_markdown(string|null $value=null, string|array $name='', array $config=[]): string
    {
        $data = $this->data();
        $object = $this->object();
        if(is_array($name)){
            $config = $name;
            $name = '';
        }
        d($value);
        d($config);
        ddd($name);
        $parser = new Module\Markdown();
        $value = $parser->parse($object, $value, $config);
        $name = trim($name,'\'"');
        if(empty($name)){
            $content = $data->data('#content');
            $content[] = $value;
            $data->data('#content', $content);
            return $value;
        } else {
            if(substr($name, 0, 1) === '$'){
                $name = substr($name, 1);
            }
            $data->data($name, $value);
        }
        return '';
    }
}