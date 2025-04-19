<?php
namespace Plugin;

use Exception;

use Raxon\Exception\ObjectException;
use Raxon\Exception\DirectoryCreateException;

use Raxon\Module\Core;
use Raxon\Module\File;

trait Ramdisk_Speedtest {

    /**
     * @throws ObjectException|
     * @throws DirectoryCreateException
     */
    protected function ramdisk_speedtest(): void
    {
        $object = $this->object();
        $id = posix_geteuid();
        if (!empty($id)){
            throw new Exception('RamDisk speedtest can only be run by root...');
        }
        if($object->config('ramdisk.url')){
            $url = $object->config('ramdisk.url') . 'speedtest';
            $command = 'dd if=/dev/zero of=' . $url . 'zero bs=4k count=100000';
            Core::execute($object, $command, $output, $notification);
            echo 'Write:' . PHP_EOL;
            if($output){
                echo $output . PHP_EOL;
            }
            if($notification){
                echo $notification . PHP_EOL;
            }
            $command = 'dd if=' . $url . 'zero of=/dev/null bs=4k count=100000';
            Core::execute($object, $command, $output, $notification);
            echo 'Read:' . PHP_EOL;
            if($output){
                echo $output . PHP_EOL;
            }
            if($notification){
                echo $notification . PHP_EOL;
            }
            File::delete($url . 'zero');
        }
    }
}