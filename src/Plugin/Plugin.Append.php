<?php
namespace Plugin;

use Raxon\App;

trait Plugin_Append {

    protected function plugin_append(string $url): void
    {
        $object = $this->object();
        $config = $object->data(App::CONFIG);
        $plugin = $config->data('parse.dir.plugin');
        $plugin[] = $url;
        $config->data('parse.dir.plugin', $plugin);
    }
}