<?php
namespace Plugin;

use Exception;

trait Html_Target_Create {

    /**
     * @throws Exception
     */
    protected function html_target_create(string $target=null, $options=[]): string
    {
        $result = $target;
        foreach($options as $key => $value){
            $result.='[' . $key . '=\'' . $value . '\']';
        }
        return $result;
    }
}