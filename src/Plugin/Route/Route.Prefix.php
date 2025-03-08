<?php
namespace Plugin;

use Raxon\Config;

trait Route_Prefix {

    protected function route_prefix(string $prefix=null): ?string
    {
        $object = $this->object();
        if($prefix !== null){
            $object->config(Config::DATA_ROUTE_PREFIX, $prefix);
        }
        return $object->config(Config::DATA_ROUTE_PREFIX);
    }
}