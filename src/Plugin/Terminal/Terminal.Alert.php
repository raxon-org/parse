<?php
namespace Plugin;

use Raxon\Module\Cli;

trait Terminal_Alert {

    protected function terminal_alert(string $text, array $options=[]): string
    {
        return Cli::alert($text, $options);
    }

}