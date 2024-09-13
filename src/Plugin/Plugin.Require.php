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

use Raxon\App;
use Raxon\Module\Data;
use Raxon\Module\Dir;
use Raxon\Module\File;

use Package\RaXon\Parse\Service\Parse;

use Exception;

trait Plugin_require {

    /**
     * @throws Exception
     */
    protected function plugin_require(string $url, mixed $data=null): mixed
    {
        $object = $this->object();
        $dir = Dir::name($object->config('package.raxon/parse.build.state.source.url'));
        if(substr($url, 0, 2) === './'){
            $url = $dir . substr($url, 2);
        }
        elseif(str_contains($url, '/') === false){
            $url = $dir . $url;
        }
        if(!File::exist($url)) {
            $text = 'Require: file not found: ' . $url . ' in template: ' . $object->config('package.raxon/parse.build.state.source.url');
            throw new Exception($text);
        }
        $mtime = File::mtime($url);
        $require_url = $object->config('package.raxon/parse.build.state.source.require.url');
        if($require_url === null){
            $require_url = [];
        }
        $require_url[] = $url;
        d($require_url);
        $object->config('package.raxon/parse.build.state.source.require.url', $require_url);
        $require_mtime = $object->config('package.raxon/parse.build.state.source.require.mtime');
        if($require_mtime === null){
            $require_mtime = [];
        }
        $require_mtime[] = $mtime;
        $object->config('package.raxon/parse.build.state.source.require.mtime', $require_mtime);
        $data = $this->plugin_require_data($data);
        $flags = App::flags($object);
        $options = App::options($object);
        $options_require = clone $options;
        $options_require->source = $url;
        unset($options_require->hash);
        unset($options_require->class);
        unset($options_require->namespace);
        $parse = new Parse($object, $data, $flags, $options_require);
        $read = File::read($url);
        $compile = $parse->compile($read);
        return $compile;
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