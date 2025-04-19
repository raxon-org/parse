<?php
namespace Plugin;

use Raxon\Module\Cli;

trait Terminal_Put {

    protected function terminal_put(string $command, array $argument=[]): int | string
    {
        return Cli::tput($command, $argument);
    }

}