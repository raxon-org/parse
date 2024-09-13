<?php
/**
 * @package Plugin\Modifier
 * @author Remco van der Velde
 * @since 2024-08-19
 * @license MIT
 * @version 1.0
 * @changeLog
 *    - all
 */
namespace Plugin;

use Raxon\Module\Data;
use Raxon\Module\File;

use Exception;

trait Plugin_require {

    /**
     * @throws Exception
     */
    protected function plugin_require(string $url, mixed $data=null): void
    {
        $object = $this->object();

        $dir = Dir::name($object->config('package.raxon/parse.build.state.source'));
        ddd($dir);

        if(!File::exist($url)) {
            $text = 'Require: file not found: ' . $url . ' in template: ' . $object->config('package.raxon/parse.build.state.source');
            throw new Exception($text);
        }
        $mtime = File::mtime($url);
        $data = $this->plugin_require_data($data);
        d($mtime);
        d($data);
        ddd($url);
    }

    /**
     * @throws Exception
     */
    protected function plugin_require_data(mixed $data=null): ?Data
    {
        if(is_array($data)){
            $data = new Data($data);
        }
        elseif(
            is_object($data) &&
            $data instanceof Data
        ){
            //nothing
        }
        elseif(is_object($data)) {
            $data = new Data($data);
        } else {
            return null;
        }
        return $data;
    }

}