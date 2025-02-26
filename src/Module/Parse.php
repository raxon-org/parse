<?php
/**
 * @author          Remco van der Velde
 * @since           04-01-2019
 * @copyright       (c) Remco van der Velde
 * @license         MIT
 * @version         1.0
 * @changeLog
 *  -    all
 */
namespace Raxon\Parse\Module;

use Raxon\App;
use Raxon\Config;

use Raxon\Module\Core;
use Raxon\Module\Data;
use Raxon\Module\Dir;
use Raxon\Module\Event;
use Raxon\Module\File;
use Raxon\Module\Parallel;

use Exception;
use ParseError;

use Raxon\Exception\FileWriteException;
use Raxon\Exception\ObjectException;
use stdClass;

class Parse {
    const PLUGIN = 'Plugin';
    const TEMPLATE = 'Template';
    const COMPILE = 'Compile';

    const THIS_RESERVED_WORDS = [
        '#parentNode',
        '#rootNode',
        '#key',
        '#attribute'
    ];
    private $object;
    private $storage;
    private $build;
    private $limit;
    private $cache_dir;
    private $local;
    private $is_assign;
    private $halt_literal;
    private $use_this;

    private $key;

    private $counter = 0;

    /**
     * @throws ObjectException
     */
    public function __construct($object, $storage=null){
        $this->object($object);
        $this->configure();
        if($storage === null){
            $this->storage(new Data());
        } else {
            $this->storage($storage);
        }
        $priority = 10;
        Event::off($object, 'parse.build.plugin.require', ['priority' => $priority]);
    }

    /**
     * @throws ObjectException
     * @throws Exception
     */
    private function configure(): void
    {
        $id = posix_geteuid();
        $config = $this->object()->data(App::NAMESPACE . '.' . Config::NAME);
        $dir_plugin = $config->data('project.dir.plugin');
        if(empty($dir_plugin)){
            $config->data('project.dir.plugin', $config->data('project.dir.source') . Parse::PLUGIN . $config->data('ds'));
        }
        $dir_plugin = $config->data('host.dir.plugin');
        if(empty($dir_plugin)){
            $config->data('host.dir.plugin', $config->data('host.dir.root') . Parse::PLUGIN . $config->data('ds'));
        }
        $dir_plugin = $config->data('framework.dir.plugin');
        if(empty($dir_plugin)){
            $config->data('framework.dir.plugin', $config->data('framework.dir.source') . Parse::PLUGIN . $config->data('ds'));
        }
        $dir_plugin = $config->data('controller.dir.plugin');
        if(empty($dir_plugin)){
            if(empty($config->data('controller.dir.root'))){
                throw new Exception('Controller dir root is not set');
            }
            $config->data('controller.dir.plugin', $config->data('controller.dir.root') . Parse::PLUGIN . $config->data('ds'));
        }
        $compile = $config->data('dictionary.compile');
        if(empty($compile)){
            $config->data('dictionary.compile', Parse::COMPILE);
        }
        $template = $config->data('dictionary.template');
        if(empty($template)){
            $config->data('dictionary.template', Parse::TEMPLATE);
        }
        if(
            $config->data('ramdisk.url') &&
            empty($config->data('ramdisk.is.disabled'))
        ){
            $cache_dir =
                $config->data('ramdisk.url') .
                $config->data(Config::POSIX_ID) .
                $config->data('ds') .
                $config->data('dictionary.compile') .
                $config->data('ds')
            ;
            Dir::create($cache_dir);
        } else {
            if($config->data(Config::POSIX_ID) === 1000){
                $cache_dir =
                    '/tmp/' .
                    $config->data(Config::POSIX_ID) .
                    $config->data('ds') .
                    $config->data('dictionary.compile') .
                    $config->data('ds');
            } else {
                $cache_dir =
                    $config->data('framework.dir.temp') .
                    $config->data(Config::POSIX_ID) .
                    $config->data('ds') .
                    $config->data('dictionary.compile') .
                    $config->data('ds');
            }

            Dir::create($cache_dir);
        }
        $this->cache_dir($cache_dir);
        $use_this = $config->data('parse.read.object.use_this');
        if(is_bool($use_this)){
            $this->useThis($use_this);
        } else {
            $this->useThis(false);
        }
    }

    public function useThis($useThis=null): mixed
    {
        if($useThis !== null){
            $this->use_this = $useThis;
        }
        return $this->use_this;
    }

    public function object(App $object=null): ?App
    {
        if($object !== null){
            $this->setObject($object);
        }
        return $this->getObject();
    }

    private function setObject(App $object=null): void
    {
        $this->object= $object;
    }

    private function getObject(): ?App
    {
        return $this->object;
    }

    public function limit($limit=null): ?array
    {
        if($limit !== null){
            $this->setLimit($limit);
        }
        return $this->getLimit();
    }

    public function setLimit($limit=null): void
    {
        $this->limit= $limit;
    }

    private function getLimit(): ?array
    {
        return $this->limit;
    }

    public function storage(object $storage=null): ?object
    {
        if($storage !== null){
            $this->setStorage($storage);
        }
        return $this->getStorage();
    }

    private function setStorage(object $storage=null): void
    {
        $this->storage = $storage;
    }

    private function getStorage(): ?object
    {
        return $this->storage;
    }

    public function build(Build $build=null): ?Build
    {
        if($build !== null){
            $this->setBuild($build);
        }
        return $this->getBuild();
    }

    private function setBuild(Build $build=null): void
    {
        $this->build= $build;
    }

    private function getBuild(): ?Build
    {
        return $this->build;
    }

    public function cache_dir($cache_dir=null): ?string
    {
        if($cache_dir !== null){
            $this->cache_dir = $cache_dir;
        }
        return $this->cache_dir;
    }

    public function local($depth=0, $local=null): ?object
    {
        if($this->local === null){
            $this->local = [];
        }
        if($local !== null){
            $this->local[$depth] = $local;
        }
        if(
            $depth !== null &&
            array_key_exists($depth, $this->local)
        ){
            return clone $this->local[$depth];
        }
        return null;
    }

    public function is_assign($is_assign=null): ?bool
    {
        if($is_assign !== null){
            $this->is_assign = $is_assign;
        }
        return $this->is_assign;
    }

    public function halt_literal($halt_literal=null): ?bool
    {
        if($halt_literal !== null){
            $this->halt_literal = $halt_literal;
        }
        return $this->halt_literal;
    }

    private static function replace_raw($string=''): string
    {
        $explode = explode('"{{raw|', $string, 2);
        if(array_key_exists(1, $explode)){
            $temp = explode('}}"', $explode[1], 2);
            if(array_key_exists(1, $temp)){
                $explode[1] = implode('}}', $temp);
                $string = implode('{{', $explode);
                return Parse::replace_raw($string);
            }
        }
        $explode = explode('"{{ raw |', $string, 2);
        if(array_key_exists(1, $explode)){
            $temp = explode('}}"', $explode[1], 2);
            if(array_key_exists(1, $temp)){
                $explode[1] = implode('}}', $temp);
                $string = implode('{{', $explode);
                return Parse::replace_raw($string);
            }
        }
        return $string;
    }

    public static function unset(object $object, object $unset): object
    {
        foreach($object as $key => $value){
            if(
                is_object($value) &&
                get_class($value) === stdClass::class
            ){
                Parse::unset($value, $unset);
            }
        }
        foreach($unset as $unset_key => $unset_value){
            unset($object->{$unset_value});
        }
        return $object;
    }

    /**
     * @throws ObjectException
     * @throws FileWriteException
     * @throws Exception
     */
    public function compile($string='', $data=[], $storage=null, $depth=null, $is_debug=false): mixed
    {
        $type = gettype($string);
        if(
            $string === null ||
            $string === '' ||
            $type === 'boolean' ||
            $type === 'integer' ||
            $type === 'double' ||
            $type === 'resource' ||
            $type === 'unknown type' ||
            (
                $type === 'array' &&
                empty($string)
            ) ||
            (
                $type === 'object' &&
                empty((array) $string)
            )
        ){
            return $string;
        }
        ob_start();
        $original = $string;
        $object = $this->object();
        if($storage === null){            
            $storage = $this->storage(new Data());
        }
        if(is_object($data)){
            $storage->data(Core::object_merge($storage->data(), $data));
        } else {
            $storage->data($data);
        }
        if($type === 'array'){
            foreach($string as $key => $value){
                $value_type = gettype($value);
                if(
                    $value === null ||
                    $value === '' ||
                    $value_type === 'boolean' ||
                    $value_type === 'integer' ||
                    $value_type === 'double' ||
                    $value_type === 'resource' ||
                    $value_type === 'unknown type' ||
                    (
                        $value_type === 'array' &&
                        empty($value)
                    ) ||
                    (
                        $value_type === 'object' &&
                        empty((array) $value)
                    )
                ){
                    $string[$key] = $value;
                } else {
                    if(
                        is_string($value) &&
                        stristr($value, '{') !== false
                    ){
                        $value = Literal::uniform($object, $value);
                        $disable_function = $this->object()->config('parse.compile.disable.function.Value::contains_replace');
                        $this->object()->config('parse.compile.disable.function.Value::contains_replace', true);
                        if(str_contains($value, 'try')){
                            $is_debug = true;
                        }
                        $disable_function_prepare = $this->object()->config('parse.compile.disable.function.Parse::prepare_code');
//                        $this->object()->config('parse.compile.disable.function.Parse::prepare_code', true);
                        $string[$key] = $this->compile($value, $storage->data(), $storage, $depth, $is_debug);
                        if($disable_function){
                            $this->object()->config('parse.compile.disable.function.Value::contains_replace', $disable_function);
                        } else {
                            $this->object()->config('delete', 'parse.compile.disable.function.Value::contains_replace');
                        }
                        if($disable_function_prepare){
                            $this->object()->config('parse.compile.disable.function.Parse::prepare_code', $disable_function_prepare);
                        } else {
                            $this->object()->config('delete', 'parse.compile.disable.function.Parse::prepare_code');
                        }
                    }
                    elseif(!is_scalar($value)){
                        $disable_function_prepare = $this->object()->config('parse.compile.disable.function.Parse::prepare_code');
//                        $this->object()->config('parse.compile.disable.function.Parse::prepare_code', false);
                        $string[$key] = $this->compile($value, $storage->data(), $storage, $depth, $is_debug);
                        if($disable_function_prepare){
                            $this->object()->config('parse.compile.disable.function.Parse::prepare_code', $disable_function_prepare);
                        } else {
                            $this->object()->config('delete', 'parse.compile.disable.function.Parse::prepare_code');
                        }
                    } else {
                        $string[$key] = $value;
                    }
                }
            }
            return $string;
        }
        elseif($type === 'object'){
            $reserved_keys = [];
            if($this->useThis() === true){
                $source = $storage->data('raxon.org.parse.view.source');
                if(empty($source)){
                    $file = $storage->data('raxon.org.parse.view.url');
                } else {
                    $file = $storage->data('raxon.org.parse.view.source.url');
                }
                if($this->key){
                    $key = $this->object()->config('parse.read.object.this.key');
                    $string->{$key} = $this->key;
//                    $storage->data($key, $this->key);
                }
                if($depth === null){
                    $depth = 0;
                    $key = $this->object()->config('parse.read.object.this.url');
                    $string->{$key} = $file;
                    $this->local($depth, $string);
                } else {
                    $depth++;
                    $this->local($depth, $string);
                }
                foreach($this->object()->config('parse.read.object.this') as $key => $value){
                    $reserved_keys[] = $value;
                }
            }
            $string_object = Core::deep_clone($string);
            $parentKey = $this->key;
            foreach($string_object as $key => $value){
                if(
                    $this->useThis() === true &&
                    in_array(
                        $key,
                        $reserved_keys,
                        true
                    )
                ){
                    continue;
                }
                try {
                    $this->key = $key;
                    $attribute = $this->object()->config('parse.read.object.this.attribute');
                    $string->{$attribute} = $key;
                    $value_type = gettype($value);
                    if(
                        $value === null ||
                        $value === '' ||
                        $value_type === 'boolean' ||
                        $value_type === 'integer' ||
                        $value_type === 'double' ||
                        $value_type === 'resource' ||
                        $value_type === 'unknown type' ||
                        (
                            $value_type === 'array' &&
                            empty($value)
                        ) ||
                        (
                            $value_type === 'object' &&
                            empty((array) $value)
                        )
                    ){
                        $string->{$key} = $value;
                    } else {
                        if(
                            is_string($value) &&
                            stristr($value, '{') !== false
                        ){
                            $value = Literal::uniform($object, $value);
                            $disable_function = $this->object()->config('parse.compile.disable.function.Value::contains_replace');
                            $disable_function_prepare = $this->object()->config('parse.compile.disable.function.Parse::prepare_code');
//                            $this->object()->config('parse.compile.disable.function.Parse::prepare_code', true);
                            ob_start();
                            $value = $this->compile($value, $storage->data(), $storage, $depth, $is_debug);
                            $ob = ob_get_contents();
                            ob_end_clean();
                            if($ob){
                                $value = $ob . $value;
                            }
                            if($disable_function){
                                $this->object()->config('parse.compile.disable.function.Value::contains_replace', $disable_function);
                            } else {
                                $this->object()->config('delete', 'parse.compile.disable.function.Value::contains_replace');
                            }
                            if($disable_function_prepare){
                                $this->object()->config('parse.compile.disable.function.Parse::prepare_code', $disable_function_prepare);
                            } else {
                                $this->object()->config('delete', 'parse.compile.disable.function.Parse::prepare_code');
                            }
                        }
                        elseif(!is_scalar($value)){
                            $disable_function_prepare = $this->object()->config('parse.compile.disable.function.Parse::prepare_code');
//                            $this->object()->config('parse.compile.disable.function.Parse::prepare_code', false);
                            ob_start();
                            $value = $this->compile($value, $storage->data(), $storage, $depth, $is_debug);
                            $ob = ob_get_clean();
                            if($disable_function_prepare){
                                $this->object()->config('parse.compile.disable.function.Parse::prepare_code', $disable_function_prepare);
                            } else {
                                $this->object()->config('delete', 'parse.compile.disable.function.Parse::prepare_code');
                            }
                        }
                        $string->{$key} = $value;
                    }
                } catch (Exception | ParseError $exception){
                    Event::trigger($object, 'parse.compile.exception', [
                        'string' => $string,
                        'data' => $data,
                        'storage' => $storage,
                        'depth' => $depth,
                        'exception' => $exception
                    ]);
                }
            }
            $this->key = $parentKey;
            /*
             * we have #parallel for parallel processing and output filter to give them the right properties.
             */

            if(property_exists($string, '#parallel')) {
                if (is_array($string->{'#parallel'})) {
                    if(Core::is_cli()){
                        //if cli else we can't do parallel
                        $threads = $object->config('parse.plugin.parallel.thread');
                        $chunks = array_chunk($string->{'#parallel'}, $threads);
                        $chunk_count = count($chunks);
                        $count = 0;
                        $done = 0;
                        $result = [];
                        $parse = clone($this);
                        foreach($chunks as $chunk_nr => $chunk) {
                            $closures = [];
                            $forks = count($chunk);
                            for ($i = 0; $i < $forks; $i++) {
                                $closures[] = function () use (
                                    $object,
                                    $parse,
                                    $chunk,
                                    $chunk_nr,
                                    $chunk_count,
                                    $i,
                                    $depth,
                                    $is_debug
                                ) {
                                    if (array_key_exists($i, $chunk)) {
                                        return $parse->compile($chunk[$i], $object->data(), $parse->storage(), $depth, $is_debug);
                                    }
                                    return null;
                                };
                            }
                            $list = Parallel::new()->execute($closures);
                            foreach($list as $key => $item){
                                if(
                                    $item !== null &&
                                    $item !== 'progress'
                                ){
                                    $result[] = $item;
                                    $count++;
                                    $done++;
                                }
                            }
                        }
                        $string->{'#parallel'} = $result;
                    } else {
                        foreach($string->{'#parallel'} as $key => $value){
                            $string->{'#parallel'}[$key] = $this->compile($value, $object->data(), $storage, $depth, $is_debug);
                        }
                    }
                }
            }
            if(property_exists($string, '#output')) {
                if (
                    is_object($string->{'#output'}) &&
                    property_exists($string->{'#output'}, 'filter')
                ) {
                    $filter = $string->{'#output'}->filter;
                    if(is_array($filter)){
                        foreach($filter as $output_filter_data){
                            $route = (object) [
                                'controller' => $output_filter_data
                            ];
                            $route = Route::controller($route);
                            if(
                                property_exists($route, 'controller') &&
                                property_exists($route, 'function')
                            ){
                                //don't check on empty $list, an output filter can have defaults...
                                try {
                                    $string = $route->controller::{$route->function}($object, $string);
                                }
                                catch(Exception $exception){
                                    d($exception);
                                }
                            }
                        }
                    }
//                    $string->result = $string->{'#parallel'};
                    //parallel must be filtered because we delete #parallel from the object
                }
            }
            //must read into it, copy should be configurable
            $copy = $this->object()->config('parse.read.object.copy');
            if($copy && is_object($copy)){
                foreach($copy as $key => $value){
                    if(property_exists($string, $key)){
                        $string->$value = $string->$key;
                    }
                }
            }
            if($depth === 0){
                $unset = $this->object()->config('parse.read.object.this');
                if($unset && is_object($unset)) {
                    $string = Parse::unset($string, $unset);
                }
            }
            return $string;
        }
        elseif($type === 'string' && stristr($string, '{') === false){
            return $string;
        } else {
            //this section takes at least 5 msec per document: file:put 2msec, opcache::put 2msec, rest 1msec
            $build = $this->build(new Build($this->object(), $this, $is_debug));
            $build->cache_dir($this->cache_dir());
            $build->limit($this->limit());
            $source = $storage->data('raxon.org.parse.view.source');
            $options = [];
            if(empty($source)){
                $options = [
                    'source' => $storage->data('raxon.org.parse.view.url')
                ];
                $url = $build->url($string, $options);
            } else {
                $options = [
                    'source' => $storage->data('raxon.org.parse.view.source.url'),
                    'parent' => $storage->data('raxon.org.parse.view.url')
                ];
                $url = $build->url($string, $options);
            }
            $string = Literal::uniform($object, $string);
            $storage->data('raxon.org.parse.compile.url', $url);
            if($this->useThis() === true){
                $storage->data('this', $this->local($depth));
                $rootNode = $this->local(0);
                if($rootNode && is_object($rootNode)){
                    $attribute = 'this.' . $this->object()->config('parse.read.object.this.rootNode');
                    $storage->data($attribute, $rootNode);
                    $key = 'this';
                    for($index = $depth - 1; $index >= 0; $index--){
                        $key .= '.' . $this->object()->config('parse.read.object.this.parentNode');
                        $storage->data($key, $this->local($index));
                    }
                }
            }
            $mtime = $storage->data('raxon.org.parse.view.mtime');
            $file_exist = File::exist($url);
            $file_mtime = false;
            if($file_exist){
                $file_mtime = File::mtime($url);
            }
            $file_mtime = false; //bug solved  pre output in the cache?
            if($file_exist && $file_mtime === $mtime){
                //cache file
                $class = $build->storage()->data('namespace') . '\\' . $build->storage()->data('class');
                try {
                    $template = new $class(new Parse($this->object()), $storage);
                    $string = $template->run();
//                    d($url);
//                    d($string);

                    $is_disabled = $this->object()->config('parse.compile.disable.function.Value::contains_replace');
//                    $is_disabled = true;
//                    $string = Parse::comment($string, 'is_disabled: ' . $is_disabled);
                    $is_disabled = true;
                    if(!$is_disabled){
                        $string = Value::contains_replace(
                            [
                                [
                                    'class',
                                    '{'
                                ],
                                [
                                    'trait',
                                    '{'
                                ],
                                [
                                    Token::TYPE_WHITESPACE,
                                    '{'
                                ],
                                /*
                                [
                                    Token::TYPE_WHITESPACE,
                                    '}'
                                ],
                                */
                            ],
                            [
                                [
                                    '{',
                                    '{' . PHP_EOL
                                ],
                                [
                                    '{',
                                    '{' . PHP_EOL
                                ],
                                [
                                    '{',
                                    '{' . PHP_EOL
                                ],
                                /*
                                [
                                    '}',
                                    '}' . PHP_EOL
                                ]
                                */
                            ],
                            $string
                        );
                        /*
                        $string = Value::contains_replace(
                            [
                                [
                                    'class',
                                    '{'
                                ],
                                [
                                    'try',
                                    '{'
                                ],
                                [
                                    '(',
                                    '{'
                                ],
                                [
                                    'else',
                                    '{'
                                ],
                                [
                                    Token::TYPE_WHITESPACE,
                                    '{'
                                ],
                                [
                                    Token::TYPE_WHITESPACE,
                                    '}'
                                ],
                            ],
                            [
                                [
                                    '{',
                                    '{' . PHP_EOL
                                ],
                                [
                                    '{',
                                    '{' . PHP_EOL
                                ],
                                [
                                    '{',
                                    '{' . PHP_EOL
                                ],
                                [
                                    '{',
                                    '{' . PHP_EOL
                                ],
                                [
                                    '{',
                                    '{' . PHP_EOL
                                ],
                                [
                                    '}',
                                    '}' . PHP_EOL
                                ]
                            ],
                            $string
                        );
                        */
                    }
                    if(empty($this->halt_literal())){
                        $string = Literal::restore($storage, $string);
                    }
                    $storage->data('delete', 'this');
                    if(
                        $this->object()->config('framework.environment') === Config::MODE_DEVELOPMENT &&
                        $this->object()->config('project.log.debug')
                    ){
                        $this->object->logger($this->object()->config('project.log.debug'))
                            ->info('cache file: ' . $url . ' mtime: ' . $mtime)
                        ;
                    }
                    if($string === 'null'){
                        return null;
                    }
                    elseif($string === 'true'){
                        return true;
                    }
                    elseif($string === 'false'){
                        return false;
                    }
                    elseif(is_numeric($string)){
                        if(trim($string, "\n\t ") === $string){
                            return $string + 0;
                        } else {
                            return $string;
                        }

                    }
                    return $string;
                }
                catch (Exception $exception){
                    return $exception;
                }

            }
            elseif(File::exist($url) && File::mtime($url) !== $mtime){
                Event::trigger($object, 'parse.compile.opcache.invalidate', [
                    'string' => $string,
                    'data' => $data,
                    'storage' => $storage,
                    'depth' => $depth,
                    'url' => $url,
                    'url_mtime' => $file_mtime,
                    'mtime' => $mtime
                ]);
                opcache_invalidate($url, true);
            }
            if(empty($this->halt_literal())){
                $string = literal::apply($storage, $string);
            }
            $string = Parse::replace_raw($string);
            $string = Parse::prepare_code($object, $storage, $string);
//            $string = ltrim($string, " \t\n\r\0\x0B"); //@disabled @ 2024-07-12
//            d($string);
            $tree = Token::tree($string, [
                'object' => $object,
                'url' => $url,
            ]);
            if(str_contains($string, '/*')){
//                ddd($tree);
            }
            try {
                $tree = $build->require('function', $tree);
                $tree = $build->require('modifier', $tree);
                $build_storage = $build->storage();
                $document = $build_storage->data('document');
                if(empty($document)){
                    $document = [];
                }
                $document = $build->create('header', $tree, $document);
                $document = $build->create('class', $tree, $document);
                $build->indent(2);
                $document = $build->document($storage, $tree, $document);
                $document = $build->create('run', $tree, $document);
                $document = $build->create('require', $tree, $document);
                $document = $build->create('use', $tree, $document);
                $document = $build->create('trait', $tree, $document);
//                d($mtime);
                $write = $build->write($url, $document, $string);
                if($mtime !== null){
                    $touch = File::touch($url, $mtime);
//                    d(File::mtime($url));
                    opcache_invalidate($url, true);
                    if(opcache_is_script_cached($url) === false){
                        $status = opcache_get_status(true);
                        if($status !== false){
                            opcache_compile_file($url);
                            Event::trigger($object, 'parse.compile.opcache.file', [
                                'string' => $string,
                                'data' => $data,
                                'storage' => $storage,
                                'depth' => $depth,
                                'url' => $url,
                                'url_mtime' => $file_mtime,
                                'mtime' => $mtime
                            ]);
                        }
                    }
                }
            }
            catch (Exception $exception){
                return $exception;
            }
            $class = $build->storage()->data('namespace') . '\\' . $build->storage()->data('class');
            try {
                $exists = class_exists($class);
                if ($exists) {
                    ob_start();
                    $template = new $class(new Parse($this->object()), $storage);
                    $string = $template->run();
                    $ob = ob_get_clean();
                    if($ob){
                        $string = $ob . $string;
                    }
                    $is_disabled = $this->object()->config('parse.compile.disable.function.Value::contains_replace');
//                    $string = Parse::comment($string, 'is_disabled: ' . $is_disabled);
                    $is_disabled = true;
                    if(!$is_disabled){
                        $string = Value::contains_replace(
                            [
                                [
                                    'class',
                                    '{'
                                ],
                                [
                                    'trait',
                                    '{'
                                ],
                                [
                                    Token::TYPE_WHITESPACE,
                                    '{'
                                ],
                                /*
                                [
                                    Token::TYPE_WHITESPACE,
                                    '}'
                                ],
                                */
                            ],
                            [
                                [
                                    '{',
                                    '{' . PHP_EOL
                                ],
                                [
                                    '{',
                                    '{' . PHP_EOL
                                ],
                                [
                                    '{',
                                    '{' . PHP_EOL
                                ],
                                /*
                                [
                                    '}',
                                    '}' . PHP_EOL
                                ]
                                */
                            ],
                            $string
                        );
                        /*
                        $string = Value::contains_replace(
                            [
                                [
                                    'class',
                                    '{'
                                ],
                                [
                                    'try',
                                    '{'
                                ],
                                [
                                    '(',
                                    '{'
                                ],
                                [
                                    'else',
                                    '{'
                                ],
                                [
                                    Token::TYPE_WHITESPACE,
                                    '{'
                                ],
                                [
                                    Token::TYPE_WHITESPACE,
                                    '}'
                                ],
                            ],
                            [
                                [
                                    '{',
                                    '{' . PHP_EOL
                                ],
                                [
                                    '{',
                                    '{' . PHP_EOL
                                ],
                                [
                                    '{',
                                    '{' . PHP_EOL
                                ],
                                [
                                    '{',
                                    '{' . PHP_EOL
                                ],
                                [
                                    '{',
                                    '{' . PHP_EOL
                                ],
                                [
                                    '}',
                                    '}' . PHP_EOL
                                ]
                            ],
                            $string
                        );
                        */
                    }
                    if (empty($this->halt_literal())) {
                        $string = Literal::restore($storage, $string);
                    }
                    if ($this->useThis() === true) {
                        $storage->data('delete', 'this');
                    }
                } else {
                    $exception = new Exception('Class (' . $class . ') doesn\'t exist');
                    Event::trigger($object, 'parse.compile.exception', [
                        'string' => $string,
                        'data' => $data,
                        'storage' => $storage,
                        'depth' => $depth,
                        'url' => $url,
                        'url_mtime' => $file_mtime,
                        'mtime' => $mtime,
                        'exception' => $exception
                    ]);
                    throw $exception;
                }
            }
            catch (Exception $exception){
                return $exception;
            }
        }
        if($string === 'null'){
            return null;
        }
        elseif($string === 'true'){
            return true;
        }
        elseif($string === 'false'){
            return false;
        }
        elseif(is_numeric($string)){
            if(trim($string, "\n\t ") === $string){
                return $string + 0;
            } else {
                return $string;
            }
        }
        $ob = ob_get_clean();
        if($ob){
            $string = $ob . $string;
        }
        return $string;
    }

    /**
     * @throws Exception
     */
    public static function readback($object, $parse, $type=null): mixed
    {
        $data = $parse->storage()->data($type);
        if(is_array($data)){
            foreach($data as $key => $value){
                $data[$key] = Literal::restore($parse->storage(), $value);
            }
        }
        elseif(is_object($data)){
            foreach($data as $key => $value){                
                $data->$key = Literal::restore($parse->storage(), $value);
            }
        } else {
            $data = Literal::restore($parse->storage(), $data);
        }
        $object->data($type, $data);
        return $data;
    }

    /**
     * @throws Exception
     */
    public static function prepare_code(App $object, $storage, $string): string
    {
        $is_disabled = $object->config('parse.compile.disable.function.Parse::prepare_code');
        if($is_disabled){
            return $string;
        }
        $string = str_replace('/*{{RAX}}*/', '{R3M}', $string); //rcss files
        $string = str_replace('{{ R3M }}', '{R3M}', $string);
        $string = str_replace('{{RAX}}', '{R3M}', $string);
        $explode = explode('{R3M}', $string, 2);
        if(array_key_exists(1, $explode)){
            if(substr($explode[1], 0, 1) === PHP_EOL){
                $string = substr($explode[1], 1);
            } else {
                $string = $explode[1];
            }
        }
        if($storage->get('ldelim') === null){
            $storage->set('ldelim','{');
        }
        if($storage->get('rdelim') === null){
            $storage->set('rdelim','}');
        }
        $uuid = Core::uuid();
        $storage->data('raxon.org.parse.compile.remove_newline', true);
        $string = str_replace(
            [
                '{',
                '}',
            ],
            [
                '[$ldelim-' . $uuid . ']',
                '[$rdelim-' . $uuid . ']',
            ],
            $string
        );
        $string = str_replace(
            [
                '[$ldelim-' . $uuid . ']',
                '[$rdelim-' . $uuid . ']',
            ],
            [
                '{$ldelim}',
                '{$rdelim}',
            ],
            $string
        );
        $string = str_replace(
            [
                '{$ldelim}{$ldelim}',
                '{$rdelim}{$rdelim}',
            ],
            [
                '{',
                '}',
            ],
            $string
        );
        return $string;
    }

    public static function finalize_code($object, $storage, $string): mixed
    {
        if(is_string($string)){
            $string = str_replace('{', '{{', $string);
            $string = str_replace('}', '}}', $string);
            $string = str_replace('{$ldelim}', '{', $string);
            $string = str_replace('{$rdelim}', '}', $string);
            $string = str_replace('{{}', '{', $string);
            $string = str_replace('{}}', '}', $string);
        }
        elseif(is_array($string)){
            foreach($string as $nr => $str){
                $string[$nr] = Parse::finalize_code($object, $storage, $str);
            }
        }
        elseif(is_object($string)){
            foreach($string as $key => $str){
                $string->{$key} = Parse::finalize_code($object, $storage, $str);
            }
        }
        return $string;
    }

    public static function comment($string=null, $comment=null): mixed
    {
        if(
            is_string($string) &&
            is_string($comment)
        ){
            $string .=
                PHP_EOL .
                '/*' .
                PHP_EOL .
                $comment .
                PHP_EOL .
                '*/';
        }

        return $string;
    }
}