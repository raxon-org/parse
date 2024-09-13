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

        $dir = Dir::name($object->config('package.raxon/parse.build.state.source'));
        if(substr($url, 0, 2) === './'){
            $url = $dir . substr($url, 2);
        }
        elseif(str_contains($url, '/') === false){
            $url = $dir . $url;
        }
        if(!File::exist($url)) {
            $text = 'Require: file not found: ' . $url . ' in template: ' . $object->config('package.raxon/parse.build.state.source');
            throw new Exception($text);
        }
        $mtime = File::mtime($url);
        $data = $this->plugin_require_data($data);
        $flags = App::flags($object);
        $options = App::options($object);
        unset($options->source);
        $parse = new Parse($object, $data, $flags, $options);
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