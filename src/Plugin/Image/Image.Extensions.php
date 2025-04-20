<?php
namespace Plugin;

trait Image_Extensions {

    protected function image_extensions(): array
    {
        $object = $this->object();
        $contentType = $object->config('contentType');
        $list = [];
        if(
            is_array($contentType) ||
            is_object($contentType)
        ){
            foreach($contentType as $key => $value){
                if(stristr($value, 'image/') !== false){
                    $list[] = $key;
                }
            }
        }
        return $list;
    }

}