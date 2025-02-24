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

use stdClass;

use Exception;

use Raxon\Exception\DirectoryCreateException;
use Raxon\Exception\FileWriteException;
trait Zip_Extract {

    /**
     * @throws DirectoryCreateException
     * @throws FileWriteException
     * @throws Exception
     */
    public function zip_extract(string $source, $target=null, $options): ?string
    {
        $object = $this->object();
        if(empty($target)){
            $target = getcwd();
        }
        if(!File::exist($source)){
            throw new Exception('Cannot find source file...');
        }
        if(
            File::exist($target) &&
            !Dir::is($target)
        ){
            if(
                (
                    property_exists($options, 'force') &&
                    $options->force === true
                ) ||
                (
                    property_exists($options, 'patch') &&
                    $options->patch === true
                )
            ){
                File::delete($target);
            } else {
                throw new Exception('Target exists directory...');
            }
        }
        $zip = new \ZipArchive();
        $zip->open($source);
        $dirList = array();
        $fileList = array();
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $node = new stdClass();
            $node->name = $zip->getNameIndex($i);
            if(substr($node->name, -1) == '/'){
                $node->type = 'dir';
            } else {
                $node->type = 'file';
            }
            $node->index = $i;
            $node->url = $target . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $node->name);
            if($node->type == 'dir'){
                $dirList[] = $node;
            } else {
                $fileList[] = $node;
            }
        }
        foreach($dirList as $dir){
            if(Dir::is($dir->url) === false){
                Dir::create($dir->url);
            }
        }
        foreach($fileList as $node){
            $stats = $zip->statIndex($node->index);
            $dir = Dir::name($node->url);
            $object->logger($object->config('project.log.node'))->info('dir', [ $dir ]);
            if(File::exist($dir) && !Dir::is($dir)){
                File::delete($dir);
                Dir::create($dir);
            }
            if(File::exist($dir) === false){
                $object->logger($object->config('project.log.node'))->info('dir create', [ $dir ]);
                Dir::create($dir);
            }
            if(File::exist($node->url)){
                File::delete($node->url);
            }
            try {
                $object->logger($object->config('project.log.node'))->info('url, index', [ $node ]);
                $data = $zip->getFromIndex($node->index);
                if($data){
                    $write = File::write($node->url, $data);
                } else {
                    $object->logger($object->config('project.log.node'))->info('cannot get from index', [ $node ]);
                    $write = false;
                }
                if($write !== false){
                    File::chmod($node->url, File::CHMOD);
                    touch($node->url, $stats['mtime']);
                } else {
                    throw new Exception('Cannot write file: ' . $node->url);
                }
            } catch (FileWriteException $exception) {
                $zip->close();
                echo $exception->getMessage() . PHP_EOL;
                return null;
            }
        }
        $zip->close();
        return $target;
    }

}