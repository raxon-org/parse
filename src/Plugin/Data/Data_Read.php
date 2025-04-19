<?php
namespace Plugin;

use Exception;
use Raxon\Exception\ObjectException;
use Raxon\Module\Core;
use Raxon\Module\File;

trait Data_Read {

    /**
     * @throws ObjectException
     * @throws Exception
     */
    protected function data_read(string $url): mixed
    {
        if(File::exist($url)){
            $mtime = File::mtime($url);
            $object = $this->object();
            $require_disabled = $object->config('require.disabled');
            if($require_disabled){
                //nothing
            } else {
                $require_url = $object->config('require.url');
                $require_mtime = $object->config('require.mtime');
                if(empty($require_url)){
                    $require_url = [];
                    $require_mtime = [];
                }
                if(
                    !in_array(
                        $url,
                        $require_url,
                        true
                    )
                ){
                    $require_url[] = $url;
                    $require_mtime[] = $mtime;
                    $object->config('require.url', $require_url);
                    $object->config('require.mtime', $require_mtime);
                }
            }
            $read = File::read($url);
            $read = Core::object($read);
            $data = $this->data();
            $data->data(Core::object_merge($data->data(), $read));
            return $read;
        } else {
            throw new Exception('Error: url:' . $url . ' not found');
        }
    }
}