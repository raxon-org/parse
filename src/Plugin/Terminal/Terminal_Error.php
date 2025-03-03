<?php
namespace Plugin;
/**
 * @package Plugin
 * @author Remco van der Velde
 * @since 2024-08-19
 * @license MIT
 * @version 1.0
 * @changeLog
 *    - all
 */

use Raxon\Module\Cli;

trait Terminal_Error {

    protected function terminal_error(string $text='', array $options=[]): string
    {
        return Cli::error($text, $options);
    }

}