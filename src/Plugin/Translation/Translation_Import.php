<?php
namespace Plugin;

use Raxon\Module\Dir;
use Raxon\Module\File;

trait Translation_Import {

    protected function translation_import(): void
    {
        $object = $this->object();
        $url = $object->config('controller.dir.data') . $object->config('dictionary.translation') . $object->config('ds');
        $dir = new Dir();
        $read = $dir->read($url);
        if($read){
            foreach($read as $nr => $file){
                $file->basename = File::basename($file->name, $object->config('extension.json'));
                $translation = $object->data_read($file->url, sha1($file->url), true);
                if($translation){
                    $object->data('translation.' . strtolower($file->basename), $translation->data());
                }
            }
        }
    }

}