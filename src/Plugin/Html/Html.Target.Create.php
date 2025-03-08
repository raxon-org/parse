<?php
namespace Plugin;

trait Html_Target_Create {

    protected function html_target_create(string $target=null, $options=[]): string
    {
        $result = $target;
        foreach($options as $key => $value){
            $result.='[' . $key . '=\'' . $value . '\']';
        }
        return $result;
    }
}