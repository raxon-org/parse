<?php
namespace Plugin;

use Exception;
use Raxon\App;
use Raxon\Exception\ObjectException;
use Raxon\Exception\DirectoryCreateException;
use Raxon\Module\Core;
use Raxon\Module\Dir;

trait Ramdisk_Mount {

    /**
     * @throws DirectoryCreateException
     * @throws ObjectException
     * @throws Exception
     */
    protected function ramdisk_mount($size='1G', $url='', $name=''): void
    {
        $object = $this->object();
        $id = posix_geteuid();
        $param_size = App::parameter($object, 'mount', 1);
        if($param_size){
            $size = $param_size;
        }
        if (!empty($id)){
            throw new Exception('RamDisk can only be created by root...');
        }
        if(empty($name)){
            $name = Core::uuid();
        }
        if(empty($url)){
            $url = $object->config('framework.dir.temp') . $name . $object->config('ds');
        }
        if(substr($url, -1) !== $object->config('ds')){
            $url .= $object->config('ds');
        }
        $uuid = $object->config('ramdisk.uuid');
        if($uuid){
            $command = Core::binary($object) .
                ' raxon/node patch -class=System.Config.Ramdisk -uuid=' .
                $uuid .
                ' -name="'.
                $name .
                '" -url="'.
                $url .
                '" -size="'.
                $size .
                '"'
            ;
            echo $command . PHP_EOL;
            Core::execute($object, $command);
        }
        Dir::create($url, 0777);
        $mount_url = substr($url, 0, -1);
        $command = 'mount -t tmpfs -o size=' . $size . ' ' . $name .' ' . $mount_url;
        echo $command . PHP_EOL;
        Core::execute($object, $command, $output, $notification);
        if($output){
            echo $output . PHP_EOL;
        }
        if($notification){
            echo $notification . PHP_EOL;
        }
        $command = 'chown www-data:www-data ' . $url;
        Core::execute($object, $command);
        $command = 'mount | tail -n 1';
        Core::execute($object, $command, $output);
        if($output){
            echo $output . PHP_EOL;
        }
    }
}