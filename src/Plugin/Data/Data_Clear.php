<?php
namespace Plugin;

trait Data_Clear {

    protected function data_clear(): void
    {
        $data = $this->data();
        $data->clear();
    }
}