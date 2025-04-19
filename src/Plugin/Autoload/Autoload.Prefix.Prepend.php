<?php
namespace Plugin;

use Raxon\App;

trait Autoload_Prefix_Prepend {

    protected function autoload_prefix_prepend(string $prefix='', array|string $directory='', array|string $extension=''): void
    {
        $object = $this->object();
        $autoload = $object->data(App::AUTOLOAD_RAXON);
        $autoload->prependPrefix($prefix, $directory, $extension);
    }
}