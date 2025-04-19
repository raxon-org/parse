<?php
namespace Plugin;

use Raxon\Module\Core;

trait Object_Select {

    protected function object_select(string $url, string $select=null, bool $compile=false, string $scope='scope:object'): object
    {
        $parse = $this->parse();
        $data = $this->data();
        return Core::object_select($parse, $data, $url, $select, $compile, $scope);
    }
}