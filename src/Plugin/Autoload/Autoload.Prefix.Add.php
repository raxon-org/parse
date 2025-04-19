<?php
namespace Plugin;

use Raxon\App;

trait Autoload_Prefix_Add {

    protected function autoload_prefix_add(string $prefix='', array|string $directory='', array|string $extension=''): void
    {
        $object = $this->object();
        $autoload = $object->data(App::AUTOLOAD_RAXON);
        $autoload->addPrefix($prefix, $directory, $extension);
    }
}