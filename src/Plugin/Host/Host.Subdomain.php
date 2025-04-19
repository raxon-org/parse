<?php
namespace Plugin;

use Raxon\Module\Host;

trait Host_Subdomain {

    protected function host_subdomain(string $host): bool|string
    {
        return Host::subdomain($host);
    }

}