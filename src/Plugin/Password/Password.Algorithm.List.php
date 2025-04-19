<?php
namespace Plugin;

trait Password_Algorithm_List {


    protected function password_algorithm_list(): array
    {
        return password_algos();
    }
}