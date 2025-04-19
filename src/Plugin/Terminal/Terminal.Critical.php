<?php
namespace Plugin;

use Raxon\Module\Cli;

trait Terminal_Critical {

    protected function terminal_critical(string $text, array $options=[]): string
    {
        return Cli::critical($text, $options);
    }

}