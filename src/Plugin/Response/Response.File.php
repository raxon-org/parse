<?php
namespace Plugin;

trait Response_File {

    protected function response_file(): void
    {
        $object = $this->object();
        $object->config('response.output', 'file');
    }
}