<?php
namespace Plugin;

use Raxon\Module\Cli;

trait Terminal_Default {

    protected function terminal_default($color, $background=null): string
    {
        return Cli::default();
    }

}