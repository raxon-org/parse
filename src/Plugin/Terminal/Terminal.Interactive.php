<?php
namespace Plugin;

use Raxon\Module\Core;


trait Terminal_Interactive {

    protected function terminal_interactive(): void
    {
        Core::interactive();
    }

}