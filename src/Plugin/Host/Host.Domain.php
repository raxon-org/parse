<?php
namespace Plugin;

use Raxon\Module\Host;

trait Host_Domain {

    protected function host_domain(string $host): bool|string
    {
        return Host::domain($host);
    }

}