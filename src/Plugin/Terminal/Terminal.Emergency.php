<?php
namespace Plugin;

use Raxon\Module\Cli;

trait Terminal_Emergency {

    protected function terminal_emergency(string $text, array $options=[]): string
    {
        return Cli::emergency($text, $options);
    }

}