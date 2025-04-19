<?php
namespace Plugin;

use Exception;
use Raxon\Module\File;

trait File_Permission {

    /**
     * @throws Exception
     */
    public function file_permission(array $list=[]): void
    {
        $object = $this->object();
        File::permission($object, $list);
    }

}