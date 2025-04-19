<?php
namespace Plugin;

use Raxon\Module\Cli;

trait Terminal_Info {

    protected function terminal_info(string $text, array $options=[]): string
    {
        return Cli::info($text, $options);
    }

}