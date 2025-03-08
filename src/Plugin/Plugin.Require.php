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

use Raxon\Config;

use Raxon\Module\Autoload;
use Raxon\Module\Data;
use Raxon\Module\Dir;
use Raxon\Module\File;
use Raxon\Module\Parse;

use Exception;

trait Plugin_require {

    /**
     * @throws Exception
     */
    protected function plugin_require(string $url, mixed $storage=null): string
    {
        $object = $this->object();
        $data = $this->data();
        $cache_url = false;
        $cache_dir = false;
        $is_cache_url = false;
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
        if(!empty($storage)){
            $data_data = new Data();
            $data_data->data($storage);
            $data_data->data('raxon.org.parse.view.source.url', $url);
            $data_data->data('ldelim', '{');
            $data_data->data('rdelim', '}');
            $data->data('raxon.org.parse.view.source.mtime', $mtime);
//        ob_start();
            $parser = new Parse($object);
            $compile =  $parser->compile($read, [], $data_data);
//        d($compile);
            $data_script = $data_data->data('script');
            $script = $data->data('script');
            if(!empty($data_script) && empty($script)){
                $data->data('script', $data_script);
            }
            elseif(!empty($data_script && !empty($script))){
                foreach($script as $nr => $value){
                    if(in_array($value, $data_script, true)){
                        unset($script[$nr]);
                    }
                }
                $data->data('script', array_merge($script, $data_script));
            }
            $data_link = $data_data->data('link');
            $link = $data->data('link');
            if(!empty($data_link) && empty($link)){
                $data->data('link', $data_link);
            }
            elseif(!empty($data_link && !empty($link))){
                foreach($link as $nr => $value){
                    if(in_array($value, $data_link, true)){
                        unset($link[$nr]);
                    }
                }
                $data->data('link', array_merge($link, $data_link));
            }
            return $compile;
        } else {
            $source = $data->data('raxon.org.parse.view.source.url');
            $data->data('raxon.org.parse.view.source.url', $url);
            $data->data('raxon.org.parse.view.source.mtime', $mtime);
            $parser = new Parse($object);
            $compile = $parser->compile($read, [], $data);
            $data->data('raxon.org.parse.view.source.url', $source);
            return $compile;
        }
    }

}