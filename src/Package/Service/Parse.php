<?php
namespace Package\Raxon\Parse\Service;

use Raxon\App;
use Raxon\Config;

use Raxon\Module\Autoload;
use Raxon\Module\Cli;
use Raxon\Module\Data;

use Plugin;

use Raxon\Module\Core;
use Raxon\Module\Dir;
use Raxon\Module\File;

use Raxon\Node\Model\Node;

use Exception;

use Raxon\Exception\ObjectException;

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
     */
    public function compile($input, $data=null){
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
        $options->hash = hash('sha256', $input);
        $object->config('package.raxon/parse.build.state.source.url', $options->source ?? 'source');
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
                $options->class
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
        $options->namespace = $options->namespace ?? 'Package\Raxon\Parse';
        ddd($options);
        $dir = $object->config('project.dir.data') .
            'Test' .
            $object->config('ds') .
            'Parse' .
            $object->config('ds');
        Dir::create($dir, Dir::CHMOD);
        $token = Token::tokenize($object, $flags, $options, $input);
        $url = $dir . $options->class . $object->config('extension.json');
        File::write($url, Core::object($token, Core::OBJECT_JSON));
        d($url);
        $url = $dir . $options->class . $object->config('extension.php');
        d($url);
        $document = Build::create($object, $flags, $options, $token);
        File::write($url, implode(PHP_EOL, $document));
        File::permission(
            $object,
            [
                'dir' => $dir,
                'url' => $url
            ]
        );
        require_once $url;
        echo PHP_EOL . str_repeat('-', Cli::tput('columns')) . PHP_EOL;
        $run = $options->namespace . '\\' . $options->class;
        $main = new $run($object, $this, $data, $flags, $options);
        return $main->run();

        /*
        // Step 2: Define the placeholder values
        $placeholders = [
            'name' => 'John Doe',
            'age' => '30',
            // Add more placeholders and their replacements as needed
        ];
        // Step 3: Replace placeholders with actual values
        foreach ($placeholders as $key => $value) {
            $template = str_replace('{{' . $key . '}}', $value, $template);
        }
        // Step 4: Output the processed template
        dd($template);
        */
    }

}