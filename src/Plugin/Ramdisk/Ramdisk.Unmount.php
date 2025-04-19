<?php
namespace Plugin;

use Exception;
use Raxon\App;
use Raxon\Exception\ObjectException;
use Raxon\Exception\DirectoryCreateException;
use Raxon\Module\Core;
use Raxon\Module\Dir;

trait Ramdisk_Unmount {

    /**
     * @throws DirectoryCreateException
     * @throws ObjectException
     * @throws Exception
     */
    protected function ramdisk_unmount(string $url=null): void
    {
        $object = $this->object();
        $object->config('ramdisk.is.disabled', true);
        $id = posix_geteuid();
        if (!empty($id)){
            throw new Exception('RamDisk can only be unmounted by root...');
        }
        if($url === null){
            $url = $object->config('ramdisk.url');
        }
        if($url !== null && $url !== ''){
            $command = 'umount ' . $url;
            Core::execute($object, $command);
            Dir::remove($url);
            //property unset of name && url of ramdisk
            $command = Core::binary($object) .
                ' raxon/node unset -class=System.Config.Ramdisk -uuid=' .
                $object->config('ramdisk.uuid') .
                ' -name -url'
            ;
            echo $command . PHP_EOL;
            Core::execute($object, $command);
            echo 'RamDisk successfully unmounted...' . PHP_EOL;
        }
    }
}