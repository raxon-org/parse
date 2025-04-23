<?php
namespace Plugin;

use Raxon\Parse\Attribute\Argument;

trait Block_Html {

    #[Argument(apply: "literal", count: 1, index:1)]
    protected function block_html($value=null, string $name=''): string
    {
        d($value);
        d($name);
        $data = $this->storage();
        $search = [" ", "\t", "\n", "\r", "\r\n"];
        $replace = ['','','','',''];
        $content_html = trim($value);
        $content_html = explode('<', $content_html);
        foreach ($content_html as $nr => $row){
            $dataRow = explode('>', $row);
            if(count($dataRow)>=2){
                foreach ($dataRow as $dataRowNr => $dataR){
                    if($dataRowNr > 0){
                        $tmp = str_replace($search, $replace, $dataR);
                    } else {
                        $tmp = $dataR;
                    }
                    if(empty($tmp)){
                        $dataRow[$dataRowNr] = '';
                    }
                }
                $content_html[$nr] = implode('>', $dataRow);
            }
        }
        $value = implode('<', $content_html);
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