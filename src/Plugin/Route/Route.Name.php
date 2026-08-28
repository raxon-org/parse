<?php
namespace Plugin;

use Raxon\Config;

trait Route_Name {

    protected function route_name(string|null $name=null): ?string
    {
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
        return $result;
    }
}