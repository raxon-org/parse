<?php
namespace Plugin;

use Raxon\Module\Cli;

trait Terminal_User_Id {

    protected function terminal_user_id(): int
    {
        return posix_geteuid();
    }

}