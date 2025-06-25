<?php
namespace Plugin;

use Raxon\Module\Cli;

trait Terminal_Default {

    protected function terminal_default(): string
    {
        return Cli::default();
    }

}