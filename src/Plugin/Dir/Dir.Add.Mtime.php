<?php
namespace Plugin;

use Raxon\Module\File;

trait Dir_Add_Mtime {

    protected function dir_add_mtime(array|object $list=[]): array|object
    {
        foreach($list as $file){
            if(
                is_object($file) &&
                property_exists($file, 'url')
            ){
                $file->mtime = File::mtime($file->url);
            }
        }
        return $list;
    }

}