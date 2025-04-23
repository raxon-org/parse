<?php
namespace Plugin;

use Raxon\Module\Core;
use Raxon\Module\File;

use Raxon\Exception\ObjectException;

use Exception;

trait Parse_Read {

    /**
     * @throws Exception
     */
    protected function parse_read(string $url, bool $cache=true): mixed
    {
        if(File::exist($url)){
            $object = $this->object();
            if($cache){
                $read = $object->compile_read($url, sha1($url));
            } else {
                $read = $object->compile_read($url);
            }
            if($read){
                try {
                    $data = $this->storage();
                    d($data->data('script'));
                    d($object->data('script'));
                    d($read);
                    $data->data(Core::object_merge($data->data(), $read->data()));
                } catch (ObjectException $e) {
                }
                return $read->data();
            }
        } else {
            throw new Exception('Error: url=' . $url . ' not found');
        }
        return '';
    }
}