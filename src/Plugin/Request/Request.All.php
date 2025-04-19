<?php
namespace Plugin;

trait Request_All {

    protected function request_all(): object
    {
        $object = $this->object();
        return $object->request();
    }
}