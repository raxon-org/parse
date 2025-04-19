<?php
namespace Plugin;

use Raxon\Module\Host;

trait Host_Extension {

    protected function host_extension(string $host): bool|string
    {
        return Host::extension($host);
    }

}