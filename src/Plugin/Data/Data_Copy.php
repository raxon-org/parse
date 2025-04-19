<?php
namespace Plugin;

trait Data_Copy {

    protected function data_copy(): void
    {
        $data = $this->data();
        $data->copy();
    }
}