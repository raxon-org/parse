<?php
namespace Plugin;

trait Is_Empty_Data {

    protected function is_empty_data(): bool
    {
        $data = $this->data();
        return $data->is_empty();
    }
}