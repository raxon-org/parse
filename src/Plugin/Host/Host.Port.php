<?php
namespace Plugin;

use Raxon\Module\Host;

trait Host_Port {

    protected function host_port(string $host): bool|null|string
    {
        return Host::port($host);
    }

}