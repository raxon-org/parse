<?php
namespace Plugin;

use Raxon\Module\Host;

trait Host_Scheme {

    protected function host_scheme(): bool|string
    {
        return Host::scheme();
    }

}