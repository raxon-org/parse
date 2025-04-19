<?php
namespace Plugin;

use Raxon\App;

trait Plugin_Prepend {

    protected function plugin_prepend(string $url): void
    {
        $object = $this->object();
        $config = $object->data(App::CONFIG);
        $plugin = $config->data('parse.dir.plugin');
        array_unshift($plugin, $url);
        $config->data('parse.dir.plugin', $plugin);
    }
}