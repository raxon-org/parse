<?php
namespace Plugin;

trait Response_Json {

    protected function response_json(): void
    {
        $object = $this->object();
        $object->config('response.output', 'json');
    }
}