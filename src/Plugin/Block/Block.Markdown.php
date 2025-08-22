<?php
namespace Plugin;

use Raxon\Parse\Attribute\Argument;

use Package\Raxon\Markdown\Module as Module;

trait Block_Markdown {

    #[Argument(apply: "literal", count: 1, index:1)]
    protected function block_markdown(string|null $value=null, string|null $name=null, array $config=[]): string
    {
        $data = $this->data();
        $object = $this->object();                
        $parser = new Module\Markdown();
        $value = $parser->parse($object, $value, $config);          
        if(
            in_array(
                $name, 
                [
                    'null', //literal null
                    ''
                ])
        ){
            $content = $data->data('#content');
            $content[] = $value;
            $data->data('#content', $content);
            return $value;
        } else {
            $name = trim($name,'\'"');
            if(substr($name, 0, 1) === '$'){
                $name = substr($name, 1);
            }
            $data->data($name, $value);
        }
        return '';
    }
}