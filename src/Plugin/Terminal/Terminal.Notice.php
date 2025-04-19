<?php
namespace Plugin;

use Raxon\Module\Cli;

trait Terminal_Notice {

    protected function terminal_notice(string $text, array $options=[]): string
    {
        return Cli::notice($text, $options);
    }

}