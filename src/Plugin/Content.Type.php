<?php
namespace Plugin;

use Raxon\App;

trait Content_Type {

    public function content_type(): string
    {
        $object = $this->object();
        return $object->data(App::CONTENT_TYPE);
    }

}