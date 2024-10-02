<?php
namespace Package\Raxon\Parse\Service;

use Raxon\App;
use Raxon\Config;

use Raxon\Module\Autoload;
use Raxon\Module\Data;

use Plugin;

use Raxon\Module\Core;
use Raxon\Module\Dir;
use Raxon\Module\File;

use Raxon\Node\Model\Node;

use Exception;

use Raxon\Exception\ObjectException;
use Raxon\Exception\TemplateException;

class Parse
{
    const NODE = 'System.Parse';
    const CONFIG = 'package.raxon/parse';


    use Plugin\Basic;

    /**
     * @throws ObjectException
     * @throws Exception
     */
    public function __construct(App $object, Data $data, $flags, $options){
        $this->object($object);
        $this->data($data);
        $this->flags($flags);
        $this->options($options);
        //move to install (config)
        $this->config();
    }

    /**
     * @throws ObjectException
     * @throws Exception
     */
    protected function config(): void
    {
        $object = $this->object();
        $node = new Node($object);
        $parse = $node->record(
            Parse::NODE,
            $node->role_system(),
            [
                'ramdisk' => true
            ]
        );
        $options = $this->options();
        $force = false;
        if(property_exists($options,'force')){
            $parse = false;
            $force = true;
        }
        if(property_exists($options, 'patch')){
            $parse = false;
        }
        if(!$parse){
            $url = $object->config('project.dir.vendor') .
                'raxon' .
                $object->config('ds') .
                'parse' .
                $object->config('ds') .
                'Data' .
                $object->config('ds') .
                Parse::NODE .
                $object->config('extension.json')
            ;
            if($force){
                $options = (object) [
                    'url' => $url,
                    'force' => true
                ];
            } else {
                $options = (object) [
                    'url' => $url,
                    'patch' => true
                ];
            }
            $response = $node->import(Parse::NODE, $node->role_system(), $options);
            $parse = $node->record(
                Parse::NODE,
                $node->role_system(),
                [
                    'ramdisk' => true
                ]
            );
        }
        $object->config(Parse::CONFIG, $parse['node']);
        $object->config(Parse::CONFIG . '.time.start', microtime(true));
    }

    /**
     * @throws Exception
     * @throws ObjectException
     * @throws TemplateException
     */
    public function compile($input, $data=null)
    {
        $start = microtime(true);
        if(is_array($data)){
            $data = new Data($data);
            $this->data($data);
        }
        elseif(
            is_object($data) &&
            !($data instanceof Data)
        ){
            $data = new Data($data);
            $this->data($data);
        } else {
            $data = $this->data();
        }
        $object = $this->object();
        $flags = $this->flags();
        $options = $this->options();
        /*
        if($object->config('package.raxon/parse.build.state.input.debug') === true){
            d($input);
            ddd($options);
        }
        */
        if(
            is_scalar($input) ||
            $input === null
        ){
            $options->hash = hash('sha256', $input);
        } else {
            $options->hash = hash('sha256', Core::object($input, Core::OBJECT_JSON_LINE));
            if(is_array($input)){

            }
            elseif(is_object($input)){
                foreach($input as $key => $value){
                    $source = $options->source ?? 'source';
                    $options->source = Core::uuid();
                    $object->config('package.raxon/parse.build.state.source.url', $options->source);
                    $object->config('package.raxon/parse.build.state.input.debug', true);
                    $object->config('package.raxon/parse.build.state.input.key', $key);
                    $attribute = $object->config('package.raxon/parse.object.this.attribute');
                    $input->{$attribute} = $key;
                    $input->{$key} = $this->compile($value, $data);
                    $options->source = $source;
                }
                ddd($input);
            }
        }
        $source = $options->source ?? 'source';
        $object->config('package.raxon/parse.build.state.source.url', $source);
        $mtime = false;
        if(
            property_exists($options, 'source') &&
            File::exist($options->source)
        ){
            $mtime = File::mtime($options->source);
        }
        $object->config('package.raxon/parse.build.state.source.mtime', $mtime);
        $class = ltrim(
            str_replace(
                [
                    '!',
                    '@',
                    '#',
                    '$',
                    '%',
                    '^',
                    '&',
                    '*',
                    '(',
                    ')',
                    '-',
                    '+',
                    '=',
                    '{',
                    '}',
                    '|',
                    ':',
                    '\'',
                    '"',
                    '<',
                    '>',
                    ',',
                    '?',
                    '/',
                    ';',
                    '.',
                    ' ',
                    '~',
                    '`',
                    '[',
                    ']',
                    '\\',
                ],
                '_',
                $object->config('package.raxon/parse.build.state.source.url')
            ),
            '_'
        );
        $cache_url = false;
        $is_plugin = false;
        $is_cache_url = false;
        $url_php = false;
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
            $options->class = Autoload::name_reducer(
                $object,
                $options->class ?? $class . '_' . $options->hash,
                $is_plugin->name_length,
                $is_plugin->name_separator,
                $is_plugin->name_pop_or_shift
            );
            $cache_url = $object->config('ramdisk.url') .
                $object->config(Config::POSIX_ID) .
                $object->config('ds') .
                $object->config('dictionary.view') .
                $object->config('ds') .
                $options->class .
                $object->config('extension.php')
            ;
            $cache_dir = Dir::name($cache_url);
            if(
                File::exist($cache_url) &&
                File::mtime($cache_url) === $mtime
            ){
                $url_php = $cache_url;
                $is_cache_url = true;
            }
        }
        $options->namespace = $options->namespace ?? 'Package\Raxon\Parse';
        if($is_cache_url === false){
            $dir = $object->config('ramdisk.url') .
                $object->config(Config::POSIX_ID) .
                $object->config('ds') .
                $object->config('dictionary.view') .
                $object->config('ds')
            ;
            Dir::create($dir, Dir::CHMOD);
            $token = Token::tokenize($object, $flags, $options, $input);
            $url_json = $dir . $options->class . $object->config('extension.json');
            File::write($url_json, Core::object($token, Core::OBJECT_JSON));
            if($cache_url){
                $url_php = $cache_url;
            } else {
                $url_php = $dir .
                    $options->class .
                    $object->config('extension.php')
                ;
            }
            if($object->config('package.raxon/parse.build.state.input.debug') === true){
                ddd($url_json);
            }
            d($url_json);
            $document = Build::create($object, $flags, $options, $token);
            File::write($url_php, implode(PHP_EOL, $document));
            File::permission(
                $object,
                [
                    'dir' => $dir,
                    'url_php' => $url_php,
                    'url_json' => $url_json
                ]
            );
            File::touch($url_json, $mtime);
            File::touch($url_php, $mtime);
        }
        if($url_php){
            d($url_php);
            $pre_require = microtime(true);
            require_once $url_php;
            $post_require = microtime(true);
            $run = $options->namespace . '\\' . $options->class;
            $main = new $run($object, $this, $data, $flags, $options);
            $result = $main->run();

            $microtime = microtime(true);
            $duration_require = round(($post_require - $pre_require) * 1000, 2) . ' ms';
            $duration_parse = round(($microtime - $post_require) * 1000, 2) . ' ms';
            $duration_script = round(($microtime - $object->config('time.start')) * 1000, 2) . ' ms';
            $microtime_explode = explode('.', $microtime);
            if(property_exists($options, 'duration')){
                $output = [
                    'class' => $class,
                    'namespace' => $options->namespace,
                    'duration' => [
                        'require' => $duration_require,
                        'parse' => $duration_parse,
                        'total' => $duration_script,
                        'finish' => date('Y-m-d H:i:s', time()) . '.' . $microtime_explode[1]
                    ]
                ];
                echo Core::object($output, Core::OBJECT_JSON) . PHP_EOL;
            }
            return $result;
        }
    }

}