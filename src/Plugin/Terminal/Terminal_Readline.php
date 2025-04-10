<?php
namespace Plugin;

use Raxon\Exception\ObjectException;

use Raxon\Module\Cli;

trait Terminal_Readline {

    /**
     * @throws ObjectException
     */
    protected function terminal_readline(string $text, string $type=null): string
    {
        if(
            $text === Cli::STREAM &&
            $type === null
        ){
            return Cli::read($text);
        }
        if($type === null){
            $type = Cli::INPUT;
        }
        return Cli::read($type, $text);
    }

}