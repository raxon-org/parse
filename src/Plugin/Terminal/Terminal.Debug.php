<?php
namespace Plugin;

use Raxon\Module\Cli;

trait Terminal_Debug {

    protected function terminal_debug(string $text, array $options=[]): string
    {
        return Cli::debug($text, $options);
    }

}