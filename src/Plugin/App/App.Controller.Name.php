<?php
namespace Plugin;

use Raxon\Module\Controller;

trait App_Controller_Name {

    public function app_controller_name(string $name='', $before=null, string $delimiter='.'): string
    {
        return Controller::name($name, $before, $delimiter);
    }

}