<?php
namespace Plugin;

trait Block_Html {

    protected function block_html(string $name='', $value=null): string
    {
        $object = $this->object();

        if($object->config('package.raxon/parse.build.use.trait_function')
        ){
            //new parser we start with the value
            $value_value = $name;
            $value = $name;
            $name = $value_value;
        }
        $data = $this->storage();
        if($value === null){
            $value = $name;
            $name = null;
        }
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
                $content[$nr] = implode('>', $dataRow);
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