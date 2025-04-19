<?php
namespace Plugin;

use Raxon\Module\Cli;

trait Terminal_Error {

    protected function terminal_error(string $text='', array $options=[]): string
    {
        return Cli::error($text, $options);
    }

}