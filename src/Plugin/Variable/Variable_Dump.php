<?php

use Raxon\Module\Cli;

trait Variable_Dump {

    protected function variable_dump(mixed $dump=null): string
    {
        return var_export($dump, true);
    }

}