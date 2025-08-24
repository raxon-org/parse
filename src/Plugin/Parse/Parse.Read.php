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
    protected function parse_read(string $url, bool $cache=true, object|null $options=null): mixed
    {
        if(File::exist($url)){
            $object = $this->object();
            $data = $this->data();
            $object_data = clone $object->data();
            $object->data(Core::object_merge($object->data(), $data->data()));              
            if($cache){
                $read = $object->compile_read($url, sha1($url), null, $options);
            } else {
                $read = $object->compile_read($url, null, null, $options);
            }            
            if($read){
                try {
                    /**
                     * $script sorting order:
                     * $script, already available script
                     * $script_merge_read, from reading the script property
                     * $script_merge, from the object (result after parsing the url (template / require inside))
                     */
                    $data = $this->storage();
                    $script = $data->data('script') ?? [];
                    $script_merge_read = $read->data('script') ?? [];
                    $script_merge = $object->data('script') ?? [];
                    $sript = array_merge($script, $script_merge_read, $script_merge);
                    if(!empty($script)){
                        $read->data('script', $script);
                    }                    
                    $link = $data->data('link') ?? [];
                    $link_merge_read = $read->data('link') ?? [];
                    $link_merge = $object->data('link') ?? [];
                    $link = array_merge($link, $link_merge_read, $link_merge);
                    if(!empty($link)){
                        $read->data('link', $link);
                    }                    
                    $data->data(Core::object_merge($data->data(), $read->data()));
                } catch (ObjectException $e) {
                }
                $object->data($object_data);
                return $read->data();
            }
        } else {
            throw new Exception('Error: url=' . $url . ' not found');
        }
        return '';
    }
}