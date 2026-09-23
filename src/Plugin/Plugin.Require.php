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
use Raxon\Config;

use Raxon\Module\Autoload;
use Raxon\Module\Data;
use Raxon\Module\Dir;
use Raxon\Module\File;
use Raxon\Parse\Module\Parse;

use Exception;

trait Plugin_Require {

    /**
     * @throws Exception
     */
    protected function plugin_require(string $url, mixed $storage=null): string
    {
        $object = $this->object();
        if($storage !== null){
            $this->storage($storage);
        }
        $data = $this->storage();
        $cache_url = false;
        $cache_dir = false;
        $is_cache_url = false;

        if(substr($url, 0, 2) == './'){
            ddd($data->data());
            $url = $data->data('raxon.org.parse.view.source.url') . substr($url, 1);
        }

        if(!File::exist($url)) {
            $text = 'Require: file not found: ' . $url . ' in template: ' . $data->data('raxon.org.parse.view.source.url');
            throw new Exception($text);
        }
        $mtime = File::mtime($url);
        if($object->config('ramdisk.url')){
            $is_plugin = false;
            $plugin_list = $object->config('cache.parse.plugin.list');
            foreach($plugin_list as $plugin){
                if(
                    property_exists($plugin, 'name') &&
                    $plugin->name === 'require'
                ){
                    $is_plugin = $plugin;
                    break;
                }
            }
            if(
                $is_plugin &&
                property_exists($is_plugin, 'name_length') &&
                property_exists($is_plugin, 'name_separator') &&
                property_exists($is_plugin, 'name_pop_or_shift')
            ){
                $cache_url = $object->config('ramdisk.url') .
                    $object->config(Config::POSIX_ID) .
                    $object->config('ds') .
                    $object->config('dictionary.view') .
                    $object->config('ds') .
                    Autoload::name_reducer(
                        $object,
                        str_replace('/', '_', $url),
                        $is_plugin->name_length,
                        $is_plugin->name_separator,
                        $is_plugin->name_pop_or_shift
                    );
                ;
                $cache_dir = Dir::name($cache_url);
                if(
                    File::exist($cache_url) &&
                    File::mtime($cache_url) === $mtime
                ){
                    $url = $cache_url;
                    $is_cache_url = true;
                }
            }
        }
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
        if(
            $is_cache_url === false &&
            $object->config('ramdisk.url') &&
            $cache_dir !== false &&
            $cache_url !== false
        ){
            //copy to ramdisk
            Dir::create($cache_dir);
            File::copy($url, $cache_url);
            File::touch($cache_url, File::mtime($url));
            exec('chmod 640 ' . $cache_url);
        }
        $source = $object->config('package.raxon/parse.build.state.source');
        $flags = App::flags($object);
        $options = (object) [];
        $options->source = $url;
//            d($data->data());
        $parser = new Parse($object, $data, $flags, $options);
        $compile = $parser->compile($read, $data);
        $object->config('package.raxon/parse.build.state.source', $source);
        return $compile;
    }

}