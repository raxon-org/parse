<?php
namespace Plugin;

trait Data_Do_Not_Nest_Key {

    protected function data_do_not_nest_key(bool $do_not_nest_key=true): void
    {
        $data = $this->data();
        $data->do_not_nest_key($do_not_nest_key);
    }
}