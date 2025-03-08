<?php
namespace PLugin;
/**
 * @package Plugin
 * @author Remco van der Velde
 * @since 2025-02-22
 * @license MIT
 * @version 1.0
 * @changeLog
 *    - all
 */

use Raxon\Module\File;
use Raxon\Module\Dir;

use Exception;

use Raxon\Exception\DirectoryCreateException;
use Raxon\Exception\FileWriteException;
trait Zip_Archive {

    /**
     * @throws DirectoryCreateException
     * @throws FileWriteException
     * @throws Exception
     */
    public function zip_archive(string $source, string $target=null): ?string
    {
        $object = $this->object();
        $parse = $this->parse();
        /*
        $limit = $parse->limit();
        $parse->limit([
            'plugin' => [
                'date'
            ]
        ]);
        */
        $data = $this->storage();
        try {
            $target = $parse->compile($target, $data);
            //$parse->setLimit($limit);
        } catch (Exception $exception) {
            echo $exception->getMessage() . PHP_EOL;
            return null;
        }
        if(
            Dir::is($source) &&
            !File::exist($target)
        ){
            $dir = new Dir();
            $read = $dir->read($source, true);
            $host = [];
            if(!is_array($read)){
                return null;
            }
            foreach($read as $file){
                $host[] = $file;
            }
            $dir = Dir::name($target);
            if(
                $dir &&
                !in_array(
                    $dir,
                    [
                        $object->config('ds')
                    ],
                    true
                )
            ){
                Dir::create($dir);
            }
            $zip = new \ZipArchive();
            $zip->open($target, \ZipArchive::CREATE);
            foreach($host as $file){
                $location = false;
                if(substr($file->url, 0, 1) === $object->config('ds')){
                    $location = substr($file->url, 1);
                } else {
                    $location = $file->url;
                }
                if(!empty($location)){
                    if($file->type === Dir::TYPE){
                        $zip->addEmptyDir($location);
                    } else {
                        $zip->addFile($source, $location);
                    }
                }
            }
            $zip->close();
            return $target;
        }
        elseif(
            File::is($source) &&
            !File::exist($target)
        ) {
            $dir = Dir::name($target);
            if(
                $dir &&
                !in_array(
                    $dir,
                    [
                        $object->config('ds')
                    ],
                    true
                )
            ){
                Dir::create($dir);
            }
            $zip = new \ZipArchive();
            $zip->open($target, \ZipArchive::CREATE);
            $location = false;
            if(substr($source, 0, 1) === $object->config('ds')){
                $location = substr($source, 1);
            } else {
                $location = $source;
            }
            if(!empty($location)){
                $zip->addFile($source, $location);
            }
            $zip->close();
        }
        return $target;
    }

}