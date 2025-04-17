<?php
namespace Plugin;

trait Block_Html {

    protected function block_html($value=null, string $name=''): string
    {
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
        if(empty($name)){
            $content = $data->data('#content');
            $content[] = $value;
            $data->data('#content', $content);
            return $value;
        } else {
            $data->data($name, $value);
        }
        return '';
    }
}