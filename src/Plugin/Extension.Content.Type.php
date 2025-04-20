<?php
namespace Plugin;

trait Extension_Content_Type {

    public function extension_content_type(string $extension): string
    {
        $object = $this->object();
        if(substr($extension,0, 1) === '.'){
            $extension = substr($extension, 1);
        }
        return $object->config('contentType.' . strtolower($extension));
    }

}