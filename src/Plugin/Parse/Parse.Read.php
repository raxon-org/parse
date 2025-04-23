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
                    /**
                     * $script, already available script
                     * $script_merge_read, from reading the script property
                     * $script_merge, from the object (result after parsing the url)
                     */
                    $data = $this->storage();
                    $script = $data->data('script') ?? [];
                    $script_merge_read = $read->data('script') ?? [];
                    $script_merge = $object->data('script') ?? [];
                    $read->data('script', array_merge($script, $script_merge_read, $script_merge));
                    $link = $data->data('link') ?? [];
                    $link_merge_read = $read->data('link') ?? [];
                    $link_merge = $object->data('link') ?? [];
                    //check sorting order
                    d($script);
                    d($script_merge);
                    ddd($script_merge_read);
                    $read->data('link', array_merge($link, $link_merge_read, $link_merge));
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