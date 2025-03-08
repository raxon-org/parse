<?php
namespace Plugin;

use Raxon\Config;

trait Route_Name {

    protected function route_name(string $name=null): ?string
    {
        return strtolower(str_replace(
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
    }
}