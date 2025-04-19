<?php
namespace Plugin;

use Exception;
use Raxon\Module\File;

trait File_Write {

    public function file_write(string $url, string $data='', array $options=[]): bool|int|null
    {
        try {
            return File::write($url, $data, $options);
        } catch (Exception $e){
            return false;
        }
    }

}