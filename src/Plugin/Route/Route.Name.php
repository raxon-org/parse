<?php
namespace Plugin;

use Raxon\Config;

trait Route_Name {

    protected function route_name(string|null $name=null): ?string
    {
        d($name);
        $result = strtolower(str_replace(
            [
                '.',
                ' '
            ],
            [
                '-',
                '-'
            ],
            $name
        ));
        d($result);
        return $result;
    }
}