<?php
namespace Plugin;

use Raxon\Module\Cli;

trait Terminal_Labels {

    protected function terminal_labels(): string
    {
        return Cli::labels();
    }

}