<?php
namespace Plugin;

trait Response_Object {

    protected function response_object(): void
    {
        $object = $this->object();
        $object->config('response.output', 'object');
    }
}