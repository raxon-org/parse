<?php
namespace Plugin;

trait Data_All {

    protected function data_all(): mixed
    {
        $data = $this->data();
        return $data->data();
    }
}