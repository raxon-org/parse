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

use Raxon\Module\Parse\Literal;
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
    public function __construct(App $object, Data $data, $flags=null, $options=null){
        $this->object($object);
        $this->data($data);
        if($flags === null){
            $flags = (object) [];
        }
        if($options === null){
            $options = (object) [];
        }
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

    public static function result($result=''){
//        d($result);
        if(is_string($result)){
            if($result === 'null'){
                return null;
            }
            elseif($result === 'true'){
                return true;
            }
            elseif($result === 'false'){
                return false;
            }
            elseif(is_numeric($result)){
                if(
                    trim($result, " \t\n\r\0\x0B") === $result &&
                    ltrim($result, '0') === $result
                ){
                    return $result + 0;
                }
            }
        }
        return $result;
    }

    public static function class_name(App $object, $class=''){
        return ltrim(
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
                $class
            ),
            '_'
        );
    }


    /**
     * @throws Exception
     * @throws ObjectException
     * @throws TemplateException
     */
    public function compile($input, $data=null, $is_debug=false): mixed
    {
        $start = microtime(true);
        if(is_array($data)){
            $data = new Data($data);
            $this->data($data);
        }
        elseif(is_object($data)){
            if($data instanceof Data){
                $this->data($data);
            } else {
                $data = new Data($data);
                $this->data($data);
            }
        } else {
            $data = $this->data();
        }
        $object = $this->object();
        $flags = $this->flags();
        $options = $this->options();

        if($is_debug){
            $object->config('package.raxon/parse.build.state.input.debug', true);
        }
        if($object->config('package.raxon/parse.build.state.input.debug') === true){
            d($input);
            ddd($options);
        }

        $depth = $options->depth ?? null;
        $type = strtolower(gettype($input));
        if(
            in_array(
                $type,
                [
                    'null',
                    'integer',
                    'double',
                    'boolean'
                ],
                true
            )
        ){
            return $input;
        }
        elseif($type === 'string'){
            /**
             * always parse the document (can have comment)
             */
            /*
            if(
                (
                    !str_contains($input, '{{') &&
                    !str_contains($input, '}}')
                ) ||
                (
                    !str_contains($input, '/*')
                ) ||
                (
                    !str_contains($input, '//')
                )
            ){
                return $input;
            }
            */
            $options->hash = hash('sha256', $input);
            //url, key & attribute might be already set.
//            $url = $data->get('this.' . $object->config('package.raxon/parse.object.this.url'));
//            $key = $data->get('this.' . $object->config('package.raxon/parse.object.this.key'));
//            $attribute = $data->get('this.' . $object->config('package.raxon/parse.object.this.attribute'));
//            $property = $data->get('this.' . $object->config('package.raxon/parse.object.this.property'));
//            $parentProperty = $data->get('this.' . $object->config('package.raxon/parse.object.this.parentProperty'));
            $data->set('this', $this->local($depth));
//            $data->set('this.' . $object->config('package.raxon/parse.object.this.url'), $url);
//            $data->set('this.' . $object->config('package.raxon/parse.object.this.attribute'), $attribute);
//            $data->set('this.' . $object->config('package.raxon/parse.object.this.property'), $property);
//            $data->set('this.' . $object->config('package.raxon/parse.object.this.parentProperty'), $parentProperty);
//            $data->set('this.' . $object->config('package.raxon/parse.object.this.key'), $key);
            $rootNode = $this->local(0);
            if(
                $rootNode &&
                is_object($rootNode)
            ){
                $key = 'this.' . $object->config('package.raxon/parse.object.this.rootNode');
                breakpoint($rootNode);
                $data->set($key, $rootNode);
                $key = 'this';
                $data->set($key, Core::object_merge($data->get($key), $this->local($depth)));
                for($index = $depth; $index >= 0; $index--){
                    $key .= '.' . $object->config('package.raxon/parse.object.this.parentNode');
                    $data->set($key, $this->local($index));
//                    $data->set($key . '.' . $object->config('package.raxon/parse.object.this.key'), $key);
//                    $data->set($key . '.' . $object->config('package.raxon/parse.object.this.property'), $parentProperty);
                }
            }
        } else {
            $options->hash = hash('sha256', Core::object($input, Core::OBJECT_JSON_LINE));
            if(is_array($input)){
                foreach($input as $key => $value){
                    $temp_source = $options->source ?? 'source';
                    $temp_class = $options->class;
                    $options->source = 'internal_' . Core::uuid();
                    $options->source_root = $temp_source;
                    $options->class = Parse::class_name($object, $options->source);
                    $data->set('this.' . $object->config('package.raxon/parse.object.this.key'), $key);
                    $input[$key] = $this->compile($value, $data, $is_debug);
                    $options->source = $temp_source;
                    $options->class = $temp_class;
                }
                $data->set('this.' . $object->config('package.raxon/parse.object.this.key', null));
                return $input;
            }
            elseif(is_object($input)){
                trace();
                d($input);
                if($depth === null){
                    $depth = 0;
                    $data->set('this.' . $object->config('package.raxon/parse.object.this.url'), $options->source ?? 'source');
                    $this->local($depth, $input);
                } else {
                    $depth++;
                    $this->local($depth, $input);
                }
                $options->depth = $depth;
                $reserved_keys = [];
                foreach($object->config('package.raxon/parse.object.this') as $key => $value){
                    $reserved_keys[] = $value;
                }
//                $attribute = $object->config('package.raxon/parse.build.state.this.attribute');
//                $property = $object->config('package.raxon/parse.build.state.this.property');
                $data->set(
                    'this.' .
                    $object->config('package.raxon/parse.object.this.parentProperty'),
                    $data->get(
                        'this.' .
                        $object->config('package.raxon/parse.object.this.property')
                    )
                );
                foreach($input as $key => $value){
                    if(
                        in_array(
                            $key,
                            $reserved_keys,
                            true
                        )
                    ){
                        continue;
                    }
                    $old_source = $options->source ?? 'source';
                    $old_class = $options->class ?? null;
                    $source = $options->source;
                    for($i = 0; $i <= $depth; $i++){
                        if($i === 0){
                            $source = str_replace('internal_', '', $source);
                        } else {
                            $source = str_replace($i . 'x_', '', $source);
                        }
                    }
                    $options->source = 'internal_' . ($depth + 1) . 'x_' . $source . '_' . $key;
                    $options->source_root = $old_source;
                    $options->class = Parse::class_name($object, $options->source);
                    $data->set('this.' . $object->config('package.raxon/parse.object.this.property'), $key);
                    $data->set('this.' . $object->config('package.raxon/parse.object.this.attribute'), $key);
                    $input->{$key} = $this->compile($value, $data, $is_debug);
                    $options->source = $old_source;
                    if($old_class){
                        $options->class = $old_class;
                    }
                }
                $options->depth--;
//                $object->config('package.raxon/parse.build.state.this.attribute', $attribute);
//                $object->config('package.raxon/parse.build.state.this.property', $property);
                return $input;
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
            $object->config('package.raxon/parse.build.state.source.mtime', $mtime);
        }
        elseif(str_starts_with($source, 'internal_')){
            $mtime = $object->config('package.raxon/parse.build.state.source.mtime');
        }
//        d($options->source);
        $object->config('package.raxon/parse.build.state.source.mtime', $mtime);
        $class = Parse::class_name($object, $object->config('package.raxon/parse.build.state.source.url'));
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
            if($is_debug){
                d($token);
            }
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
            $document = Build::create($object, $flags, $options, $token);
            d($url_php);
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
            return Parse::result($result);
        }
        return null;
    }

    /**
     * @throws Exception
     */
    public static function readback($object, $parse, $type=null): mixed
    {
        $data = $parse->data();
        $object->data($type, $data->get($type));
        return $data->get($type);
    }
}