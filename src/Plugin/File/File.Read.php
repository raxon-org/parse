<?php
namespace Plugin;

use Raxon\Module\File;

trait File_Read {

    public function file_read(string $url=null, array $options=[]): array | string
    {
        $object = $this->object();
        if(File::exist($url)){
            $mtime = File::mtime($url);
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
        }
        return File::read($url, $options);
    }

}