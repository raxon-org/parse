<?php
namespace Plugin;

trait Data_Reset {

    protected function data_reset(bool $to_empty=false): void
    {
        $data = $this->data();
        $data->reset($to_empty);
    }
}