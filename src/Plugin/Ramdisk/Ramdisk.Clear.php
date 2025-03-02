<?php
namespace Plugin;

use Exception;

use Raxon\Exception\ObjectException;
use Raxon\Exception\DirectoryCreateException;

use Raxon\Module\Core;
use Raxon\Module\Dir;

use Raxon\Node\Module\Node;

trait Ramdisk_Clear {

    /**
     * @throws ObjectException|
     * @throws DirectoryCreateException
     */
    protected function ramdisk_clear(): void
    {
        $object = $this->object();
        $object->config('ramdisk.is.disabled', true);
        $id = posix_geteuid();
        if (!empty($id)){
            throw new Exception('RamDisk clear can only be run by root...');
        }
        $class = 'System.Config.Ramdisk';
        $node = new Node($object);

        $record = $node->record($class, $node->role_system(),[]);
        if(!$record){
            throw new Exception('RamDisk not configured...');
        }
        if(!property_exists($record['node'], 'uuid')){
            throw new Exception('RamDisk not configured...');
        }
        $size = 0;
        if(property_exists($record['node'], 'size')){
            $size = $record['node']->size;
        }
        if(property_exists($record['node'], 'url')){
            $url = $record['node']->url;
            $command = 'umount ' . $url;
            Core::execute($object, $command);
            Dir::remove($url);
        }
        $name = Core::uuid();
        $url = $object->config('framework.dir.temp') . $name . $object->config('ds');
        Dir::create($url, Dir::CHMOD);
        $command = 'mount -t tmpfs -o size=' . $size . ' ' . $name .' ' . $url;
        Core::execute($object, $command);
        $command = 'chown www-data:www-data ' . $object->config('framework.dir.temp');
        Core::execute($object, $command);
        $command = 'chown www-data:www-data ' . $url;
        Core::execute($object, $command);
        $node->patch($class, $node->role_system(), [
            'uuid' => $record['node']->uuid,
            'size' => $size,
            'url' => $url,
            'name' => $name
        ]);
        $dir = new Dir();
        $read = $dir->read($object->config('framework.dir.temp'));
        if(is_array($read)){
            foreach ($read as $file){
                if(
                    $file->type === Dir::TYPE &&
                    $file->name !== $name &&
                    Core::is_uuid($file->name)
                ){
                    Dir::remove($file->url);
                    echo 'Removed: ' . $file->url . PHP_EOL;
                }
            }
        }
        echo 'Location: ' . $url . PHP_EOL;
        $command = 'mount | tail -n 1';
        Core::execute($object, $command, $output);
        echo $output . PHP_EOL;
    }
}