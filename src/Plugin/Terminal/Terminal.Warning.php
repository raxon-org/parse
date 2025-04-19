<?php
namespace Plugin;

use Raxon\Module\Cli;

trait Terminal_Warning {

    protected function terminal_warning(string $text, array $options=[]): string
    {
        return Cli::warning($text, $options);
    }

}