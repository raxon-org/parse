<?php
namespace Raxon\Parse\Module;

use Raxon\App;

use Raxon\Module\Autoload;
use Raxon\Module\Core;
use Raxon\Module\File;

use Plugin;
use Exception;
use ReflectionClass;

use Raxon\Exception\LocateException;
use Raxon\Exception\TemplateException;

class Compile
{
    use Plugin\Format_code;
    use Plugin\Basic;

    public function __construct(App $object, $flags, $options)
    {
        $this->object($object);
        $this->parse_flags($flags);
        $this->parse_options($options);
    }

    /**
     * @throws Exception
     */
    public static function create(App $object, $flags, $options, $tags=[]): array
    {
        $options->class = $options->class ?? 'Main';
        Compile::document_default($object, $flags, $options);
        $data = Compile::data_tag($object, $flags, $options, $tags);
        $data = [];
        $document = Compile::document_header($object, $flags, $options);
        $document = Compile::document_use($object, $flags, $options, $document, 'package.raxon/parse.build.use.class');
        $document[] = '';
        $document[] = 'class '. $options->class .' {';
        $document[] = '';
        $object->config('package.raxon/parse.build.state.indent', 1);
        //indent++
        $document = Compile::document_use($object, $flags, $options, $document, 'package.raxon/parse.build.use.trait');
        $document[] = '';
        $document = Compile::document_construct($object, $flags, $options, $document);
        $document[] = '';
//        d($data);
        $document = Compile::document_run($object, $flags, $options, $document, $data);
        $document[] = '}';
        return $document;
    }


    /**
     * @throws LocateException
     */
    public static function data_tag(App $object, $flags, $options , $tags=[]): array
    {
        $data = [];
        $collection = [];
        $is_script = 0;
        $script_method = false;
        $line = '';
        foreach($tags as $row_nr => $list) {
            foreach ($list as $nr => $record) {
                if (
                    array_key_exists('marker', $record) &&
                    array_key_exists('is_close', $record['marker']) &&
                    array_key_exists('name', $record['marker']) &&
                    $record['marker']['is_close'] === true &&
                    $record['marker']['name'] === 'script'
                ) {
                    $is_script--;
                    if ($is_script === 0) {
                        d($script_method);
                        ddd($collection);
                        $script_method = false;
                    }
                }
                elseif (
                    array_key_exists('method', $record) &&
                    array_key_exists('name', $record['method'])
                ) {
                    if (
                        in_array(
                            $record['method']['name'],
                            [
                                'script'
                            ],
                            true
                        )
                    ) {
                        $is_script++;
                        if($script_method === false){
                            $script_method = $record;
                        }
                        continue;
                    } else {
                        $method = '$this->' . $record['method']['name'] . '(';
                        $method .= Compile::argument($object, $flags, $options, $record, $before, $after);
                        $method .= ')';
                        $line = $method;
                    }
                }
                elseif(array_key_exists('text', $record)){
                    $line = Compile::text($object, $flags, $options, $record);
                } else {
                    ddd($record);
                }
                if ($is_script > 0) {
                    $collection[] = $line;
                }
            }
        }
        return $data;
    }

    /**
     * @throws Exception
     */
    public static function text(App $object, $flags, $options, $record = [], $variable_assign_next_tag = false): bool | string
    {
        $is_echo = $object->config('package.raxon/parse.build.state.echo');
        $ltrim = $object->config('package.raxon/parse.build.state.ltrim');
        $skip_space = $ltrim * 4;
        $skip = 0;
        if($is_echo !== true){
            return false;
        }
        if(
            array_key_exists('text', $record) &&
            $record['text'] !== ''
        ){
            $is_single_quote = false;
            $is_double_quote = false;
            $data = mb_str_split($record['text']);
            $line = '';
            $result = [];
            $is_comment = false;
            $is_comment_multiline = false;
            $is_doc_comment = false;
            foreach($data as $nr => $char){
                if($skip > 0){
                    $skip--;
                    continue;
                }
                $previous = $data[$nr - 1] ?? null;
                $next = $data[$nr + 1] ?? null;
                $next_next = $data[$nr + 2] ?? null;
                if(
                    $is_single_quote === false &&
                    $is_double_quote === false &&
                    $char === '\'' &&
                    $previous !== '\\'
                ){
                    $is_single_quote = true;
                }
                elseif(
                    $is_single_quote === true &&
                    $is_double_quote === false &&
                    $char === '\'' &&
                    $previous !== '\\'
                ){
                    $is_single_quote = false;
                }
                elseif(
                    $is_single_quote === false &&
                    $is_double_quote === false &&
                    $char === '"' &&
                    $previous !== '\\'
                ){
                    $is_double_quote = true;
                }
                elseif(
                    $is_single_quote === false &&
                    $is_double_quote === true &&
                    $char === '"' &&
                    $previous !== '\\'
                ){
                    $is_double_quote = false;
                }
                elseif(
                    $is_single_quote === false &&
                    $is_double_quote === false &&
                    $char === "\n"
                ){
                    if(
                        $is_comment === true &&
                        $is_comment_multiline === false
                    ){
                        $is_comment = false;
                        continue;
                    }
                    elseif(
                        $is_comment === true &&
                        $is_comment_multiline === true
                    ){
                        //nothing
                    }
                    elseif(
                        !in_array(
                            $line,
                            [
                                '',
                                "\r",
                            ],
                            true
                        )
                    ){
                        $result[] = 'echo \'' . str_replace(['\\','\''], ['\\\\', '\\\''], $line) . '\';' . PHP_EOL;
                    }
                    $line = '';
                    $skip_space = $ltrim * 4;
                }
                elseif(
                    $is_single_quote === false &&
                    $is_double_quote === false &&
                    $char === ' ' && $skip_space > 0
                ){
                    $skip_space--;
                    continue;
                }
                elseif(
                    $is_single_quote === false &&
                    $is_double_quote === false &&
                    $char !== ' '
                ){
                    if($skip_space > 0){
                        $line .= str_repeat(' ', (($ltrim * 4) - $skip_space));
                    }
                    $skip_space = 0;
                }
                if(
                    $is_single_quote === false &&
                    $is_double_quote === false &&
                    $char === '/' &&
                    $next === '*'
                ){
                    $is_comment = true;
                    $is_comment_multiline = true;
                    if(
                        !in_array(
                            $line,
                            [
                                '',
                                "\r",
                            ],
                            true
                        )
                    ){
                        $result[] = 'echo \'' . str_replace(['\\','\''], ['\\\\', '\\\''], $line) . '\';' . PHP_EOL;
                    }
                    $line = '';
                }
                elseif(
                    $is_single_quote === false &&
                    $is_double_quote === false &&
                    $char === '/' &&
                    $next === '/' &&
                    in_array(
                        $previous,
                        [
                            null,
                            ' ',
                            "\t",
                            "\n",
                            '}',
                        ],
                        true
                    )
                ){
                    $is_comment = true;
                    if(
                        !in_array(
                            $line,
                            [
                                '',
                                "\r",
                            ],
                            true
                        )
                    ){
                        $result[] = 'echo \'' . str_replace(['\\','\''], ['\\\\', '\\\''], $line) . '\';' . PHP_EOL;
                    }
                    $line = '';
                }
                elseif(
                    $is_single_quote === false &&
                    $is_double_quote === false &&
                    $char === '*' &&
                    $next === '/' &&
                    $is_comment_multiline = true
                ){
                    $is_comment = false;
                    $is_comment_multiline = false;
                    $skip++;
                    if($next_next === "\n"){
                        $skip++;
                    }
                }
                if(
                    $is_comment === false &&
                    $skip === 0
                ){
                    if($variable_assign_next_tag === false){
                        $line .= $char;
                    }
                    elseif(
                        $variable_assign_next_tag === true &&
                        $char === "\n"
                    ){
                        $variable_assign_next_tag = false;
                    }
                    elseif($variable_assign_next_tag === true){
                        $line .= $char;
                        if(
                            !in_array(
                                $char,
                                [
                                    ' ',
                                    "\t"
                                ],
                                true
                            )
                        ){
                            $variable_assign_next_tag = false;
                        }
                    }
                }
            }
            if($line !== ''){
                if(
                    !in_array(
                        $line,
                        [
                            '',
                            "\r",
                        ],
                        true
                    )
                ){
                    $result[] = 'echo \'' . str_replace(['\\','\''], ['\\\\', '\\\''], $line) . '\';' . PHP_EOL;
                }
            }
            if(array_key_exists(1, $result)){
//                return implode('echo "\n";' . PHP_EOL, $result);
                return implode(PHP_EOL, $result);
            }
            return $result[0] ?? false;
        }
        return false;
    }

    /**
     * @throws Exception
     * @throws LocateException
     */
    public static function argument(App $object, $flags, $options, $record=[], &$before=[], &$after=[]): string
    {
        $is_argument = false;
        $argument_value = '';
        $previous_count = 0;
        $use_trait = $object->config('package.raxon/parse.build.use.trait');
        $use_trait_function = $object->config('package.raxon/parse.build.use.trait_function');
        $argument_is_reference = [];
        $argument_attribute = (object) [];
        $attributes = false;
        $attributes_transfer = false;
        if(
            array_key_exists('method', $record) &&
            array_key_exists('name', $record['method']) &&
            is_array($use_trait_function)
        ){
            $method_match = str_replace('.', '_', strtolower($record['method']['name']));
            if(
                in_array(
                    $method_match,
                    [
                        'default',
                        'object',
                        'echo',
                        'parse',
                        'break',
                        'continue',
                        'constant',
                        'require',
                        'unset'
                    ],
                    true
                )
            ){
                $method_match = 'plugin_' . $method_match;
            }
            $key = array_search($method_match, $use_trait_function, true);
            $trait = $use_trait[$key] ?? null;
            $reflection = new ReflectionClass($trait);
            $trait_methods = $reflection->getMethods();
            foreach($trait_methods as $nr => $method){
                if(
                    strtolower($method->name) === $method_match
                ){
                    $attributes = $method->getAttributes();
                    foreach($attributes as $attribute_nr => $attribute){
                        $instance = $attribute->newInstance();
                        $instance->class = get_class($instance);
                        if($instance->class === 'Raxon\\Attribute\\Argument'){
                            $argument_attribute = $instance;
                        }
                        $attributes[$attribute_nr] = $instance;
                    }
                    $parameters = $method->getParameters();
                    foreach($parameters as $parameter_nr => $parameter){
                        if($parameter->isPassedByReference()){
                            $argument_is_reference[$parameter_nr] = true;
                        } else {
                            $argument_is_reference[$parameter_nr] = false;
                        }
                    }
                }
            }
        }
        foreach($record['method']['argument'] as $nr => $argument) {
            if(
                array_key_exists('array', $argument) &&
                is_array($argument['array']) &&
                array_key_exists(0, $argument['array']) &&
                is_array($argument['array'][0]) &&
                array_key_exists('value', $argument['array'][0]) &&
                array_key_exists(1, $argument['array']) &&
                is_array($argument['array'][1]) &&
                array_key_exists('value', $argument['array'][1]) &&
                array_key_exists(2, $argument['array']) &&
                is_array($argument['array'][2]) &&
                array_key_exists('type', $argument['array'][2]) &&
                $argument['array'][2]['type'] === 'method'
            ) {
                $name = $argument['array'][0]['value'];
                $name .= $argument['array'][1]['value'];
                $class_static = Compile::class_static($object);
                if(
                    in_array(
                        $name,
                        $class_static,
                        true
                    )
                ) {
                    $name .= $argument['array'][2]['method']['name'];
                    $argument = $argument['array'][2]['method']['argument'];
                    $use_trait = $object->config('package.raxon/parse.build.use.trait');
                    $trait = 'Plugin\\Validate';
                    if(
                        $attributes !== false &&
                        !in_array($trait, $use_trait, true)
                    ){
                        $attributes_transfer =  Core::object($attributes, Core::TRANSFER);
                        $use_trait[] = $trait;
                        $object->config('package.raxon/parse.build.use.trait', $use_trait);
                    }

                    foreach ($argument as $argument_nr => $argument_record) {
                        $value = Compile::value($object, $flags, $options, $record, $argument_record, $is_set, $before,$after);
                        $uuid_variable = Core::uuid_variable();
                        $before[] = $uuid_variable . ' = ' . $value . ';';
                        if($attributes){
                            //need use_trait (config)
                            $before[] = '$this->validate(' . $uuid_variable . ', \'argument\', Core::object(\'' . $attributes_transfer . '\', Core::FINALIZE), ' . $argument_nr . ');';
                        }
                        $value = $uuid_variable;
                        $argument[$argument_nr] = $value;
                        /*
                        if(
                            array_key_exists($argument_nr, $argument_is_reference) &&
                            $argument_is_reference[$nr] === true
                        ){
                            $after[$nr] = '$data->set(\'' .  $after[$nr] . '\', ' . $uuid_variable . ');';
                        } else {
                            $after[$nr] = null;
                        }
                        */

                        $after[$argument_nr] = null;
                    }
                    ddd($before);
                }
                if (array_key_exists(0, $argument)) {
                    $argument = $name . '(' . implode(', ', $argument) . ')';
                } else {
                    $argument = $name . '()';
                }
            } else {
                if(
                    property_exists($argument_attribute, 'apply') &&
                    $argument_attribute->apply === 'literal' &&
                    property_exists($argument_attribute, 'count') &&
                    $argument_attribute->count === '*'
                ){
                    //all arguments are literal
                    $argument = '\'' . str_replace(['\\','\''], ['\\\\', '\\\''], trim($argument['string'])) . '\'';
                }
                elseif(
                    property_exists($argument_attribute, 'apply') &&
                    $argument_attribute->apply === 'literal' &&
                    property_exists($argument_attribute, 'index') &&
                    is_array($argument_attribute->index) &&
                    in_array(
                        $nr,
                        $argument_attribute->index,
                        true
                    )
                ){
                    //we have multiple indexes
                    $argument = '\'' . str_replace(['\\','\''], ['\\\\', '\\\''], trim($argument['string'])) . '\'';
                }
                elseif (
                    property_exists($argument_attribute, 'apply') &&
                    $argument_attribute->apply === 'literal' &&
                    property_exists($argument_attribute, 'index') &&
                    is_int($argument_attribute->index) &&
                    $argument_attribute->index === $nr
                ){
                    //we have a single index
                    $argument = '\'' . str_replace(['\\','\''], ['\\\\', '\\\''], trim($argument['string'])) . '\'';
                } else {
                    $argument = Compile::value($object, $flags, $options, $record, $argument, $is_set, $before, $after);
                    $uuid_variable = Core::uuid_variable();
                    $before[] = $uuid_variable . ' = ' . $argument . ';';
                    if($attributes !== false){
                        $use_trait = $object->config('package.raxon/parse.build.use.trait');
                        $trait = 'Plugin\\Validate';
                        if($attributes !== false && !in_array($trait, $use_trait, true)){
                            $use_trait[] = $trait;
                            $object->config('package.raxon/parse.build.use.trait', $use_trait);
                            $attributes_transfer =  Core::object($attributes, Core::TRANSFER);
                        }
                        $attributes_transfer =  Core::object($attributes, Core::TRANSFER);
                        $before[] = '$this->validate(' . $uuid_variable . ', \'argument\', Core::object(\'' . $attributes_transfer . '\', Core::FINALIZE), ' . $nr . ');';
                    }
                    $argument = $uuid_variable;
                    if(
                        array_key_exists($nr, $argument_is_reference) &&
                        $argument_is_reference[$nr] === true &&
                        array_key_exists('attribute', $after[$nr])
                    ){
                        $after[$nr] = '$data->set(\'' .  $after[$nr]['attribute'] . '\', ' . $uuid_variable . ');';
                    } else {
                        $after[$nr] = null;
                    }
                }
            }
            if($argument !== ''){
                $argument_value .= $argument  . ', ';
                $is_argument = true;
            }
        }
        if($is_argument){
            $argument_value = mb_substr($argument_value, 0, -2);
        }
        return $argument_value;
    }
    
    /**
     * @throws Exception
     */
    public static function document_construct(App $object, $flags, $options, $document = []): array
    {
        $indent = $object->config('package.raxon/parse.build.state.indent');
        $document[] = str_repeat(' ', $indent * 4) . 'public function __construct(App $object, Parse $parse, Data $data, $flags, $options){';
        $object->config(
            'package.raxon/parse.build.state.indent',
            $object->config('package.raxon/parse.build.state.indent') + 1
        );
        $indent = $object->config('package.raxon/parse.build.state.indent');
        $document[] = str_repeat(' ', $indent * 4) . '$this->object($object);';
        $document[] = str_repeat(' ', $indent * 4) . '$this->parse($parse);';
        $document[] = str_repeat(' ', $indent * 4) . '$this->data($data);';
        $document[] = str_repeat(' ', $indent * 4) . '$this->parse_flags($flags);';
        $document[] = str_repeat(' ', $indent * 4) . '$this->parse_options($options);';
        $object->config(
            'package.raxon/parse.build.state.indent',
            $object->config('package.raxon/parse.build.state.indent') - 1
        );
        $indent = $object->config('package.raxon/parse.build.state.indent');
        $document[] = str_repeat(' ', $indent * 4) . '}';
        return $document;
    }

    /**
     * @throws Exception
     */
    public static function document_header(App $object, $flags, $options): array
    {
        $source = $options->source ?? '';
        $time = microtime(true);
        $object->config('package.raxon/parse.build.state.source.url', $source);
        $object->config('package.raxon/parse.build.state.indent', 0);
        $document[] = '<?php';
        $document[] = '/**';
        $document[] = ' * @package Package\Raxon\Parse';
        $document[] = ' * @license MIT';
        $document[] = ' * @version ' . $object->config('framework.version');
        $document[] = ' * @author ' . 'Remco van der Velde (remco@universeorange.com)';
        $document[] = ' * @compile-date ' . date('Y-m-d H:i:s');
        $document[] = ' * @compile-time ' . round(($time - $object->config('package.raxon/parse.time.start')) * 1000, 3) . ' ms';
        $document[] = ' * @note compiled by ' . $object->config('framework.name') . ' ' . $object->config('framework.version');
        $document[] = ' * @url ' . $object->config('framework.url');
        $document[] = ' * @source ' . $source;
        $document[] = ' */';
        $document[] = '';
        $document[] = 'namespace Package\Raxon\Parse;';
        $document[] = '';
        return $document;
    }

    public static function document_run_throw(App $object, $flags, $options, $document=[]): array
    {
        $indent = $object->config('package.raxon/parse.build.state.indent');
        $throws = $object->config('package.raxon/parse.build.run.throw');
        if(is_array($throws)){
            $document[] = str_repeat(' ', $indent * 4) . '/**';
            foreach($throws as $throw){
                $document[] = str_repeat(' ', $indent * 4) . ' * @throws ' . $throw;
            }
            $document[] = str_repeat(' ', $indent * 4) . ' */';
        }
        return $document;
    }

    public static function document_run(App $object, $flags, $options, $document = [], $data = []): array
    {
        $build = new Compile($object, $flags, $options);
        $indent = $object->config('package.raxon/parse.build.state.indent');
        $document = Compile::document_run_throw($object, $flags, $options, $document);
        $document[] = str_repeat(' ', $indent * 4) . 'public function run(): mixed';
        $document[] = str_repeat(' ', $indent * 4) . '{';
        $indent++;
        $document[] = str_repeat(' ', $indent * 4) . 'ob_start();';
        $document[] = str_repeat(' ', $indent * 4) . '$object = $this->object();';
        $document[] = str_repeat(' ', $indent * 4) . '$parse = $this->parse();';
        $document[] = str_repeat(' ', $indent * 4) . '$data = $this->data();';
        $document[] = str_repeat(' ', $indent * 4) . '$flags = $this->parse_flags();';
        $document[] = str_repeat(' ', $indent * 4) . '$options = $this->parse_options();';
        $document[] = str_repeat(' ', $indent * 4) . '$options->debug = true;';
        $document[] = str_repeat(' ', $indent * 4) . 'if (!($object instanceof App)) {';
        $indent++;
        $document[] = str_repeat(' ', $indent * 4) . 'throw new TemplateException(\'$object is not an instance of Raxon\App\');';
        $indent--;
        $document[] = str_repeat(' ', $indent * 4) . '}';
        $document[] = str_repeat(' ', $indent * 4) . 'if (!($parse instanceof Parse)) {';
        $indent++;
        $document[] = str_repeat(' ', $indent * 4) . 'throw new TemplateException(\'$parse is not an instance of Package\Raxon\Parse\Service\Parse\');';
        $indent--;
        $document[] = str_repeat(' ', $indent * 4) . '}';
        $document[] = str_repeat(' ', $indent * 4) . 'if (!($data instanceof Data)) {';
        $indent++;
        $document[] = str_repeat(' ', $indent * 4) . 'throw new TemplateException(\'$data is not an instance of Raxon\Module\Data\');';
        $indent--;
        $document[] = str_repeat(' ', $indent * 4) . '}';
        $document[] = str_repeat(' ', $indent * 4) . 'if (!is_object($flags)) {';
        $indent++;
        $document[] = str_repeat(' ', $indent * 4) . 'throw new TemplateException(\'$flags is not an object\');';
        $indent--;
        $document[] = str_repeat(' ', $indent * 4) . '}';
        $document[] = str_repeat(' ', $indent * 4) . 'if (!is_object($options)) {';
        $indent++;
        $document[] = str_repeat(' ', $indent * 4) . 'throw new TemplateException(\'$options is not an object\');';
        $indent--;
        $document[] = str_repeat(' ', $indent * 4) . '}';
        $document = Compile::format($build, $document, $data, $indent);
        $document[] = str_repeat(' ', $indent * 4) . 'if(ob_get_level() >= 1){';
        $indent++;
        $document[] = str_repeat(' ', $indent * 4) . 'return ob_get_clean();';
        $indent--;
        $document[] = str_repeat(' ', $indent * 4) . '}';
        $document[] = str_repeat(' ', $indent * 4) . 'return null;';
        $indent--;
        $document[] = str_repeat(' ', $indent * 4) . '}';
        return $document;
    }

    public static function format(Compile $build, $document=[], $data=[], $indent=2): array
    {
        $format_options = (object) [
            'indent' => $indent,
            'tag' => (object) [
                'open' => [
                    '{',
                    '[',
                ],
                'close' => [
                    '}',
                    ']',
                ]
            ],
            'parentheses' => true
        ];
        $code = $build->format_code($data, $format_options);
        foreach($code as $nr => $line){
            $document[] = $line;
        }
        return $document;
    }

    /**
     * @throws Exception
     */
    public static function document_default(App $object, $flags, $options): void
    {
        $use_class = $object->config('package.raxon/parse.build.use.class');
        if(empty($use_class)){
            $use_class = [];
            $use_class[] = 'Raxon\App';
            $use_class[] = 'Raxon\Module\Data';
            $use_class[] = 'Package\Raxon\Parse\Service\Parse';
            $use_class[] = 'Plugin';
            $use_class[] = 'Exception';
            $use_class[] = 'Raxon\Exception\TemplateException';
            $use_class[] = 'Raxon\Exception\LocateException';
        }
        $object->config('package.raxon/parse.build.use.class', $use_class);
        $use_trait = $object->config('package.raxon/parse.build.use.trait');
        if(empty($use_trait)){
            $use_trait = [];
            $use_trait[] = 'Plugin\Basic';
            $use_trait[] = 'Plugin\Parse';
            $use_trait[] = 'Plugin\Value';
        }
        $object->config('package.raxon/parse.build.use.trait', $use_trait);
        $object->config('package.raxon/parse.build.state.echo', true);
        $object->config('package.raxon/parse.build.state.indent', 2);
    }

    /**
     * @throws Exception
     */
    public static function document_use(App $object, $flags, $options, $document = [], $attribute=''): array
    {
        $use_class = $object->config($attribute);
        $indent = $object->config('package.raxon/parse.build.state.indent');
        if($use_class){
            foreach($use_class as $nr => $use){
                if(empty($use)){
                    $document[] = '';
                } else {
                    $document[] = str_repeat(' ', $indent * 4) . 'use ' . $use . ';';
                }
            }
        }
        return $document;
    }

    /**
     * @throws Exception
     * @throws LocateException
     */
    public static function value(App $object, $flags, $options, $tag, $input, &$is_set=false, &$before=[], &$after=[]): string
    {
        $source = $options->source ?? '';
        $value = '';
        $skip = 0;
        $input = Compile::value_single_quote($object, $flags, $options, $input);
        $input = Compile::value_set($object, $flags, $options, $input, $is_set);
        $is_double_quote = false;
        $double_quote_previous = false;
        $is_cast = false;
        $is_clone = false;
        $is_single_line = false;
        $is_static_class_call = false;
//        d($tag);
//        breakpoint($input);
        foreach($input['array'] as $nr => $record){
            if($skip > 0){
                $skip--;
                continue;
            }
            $previous = Token::item($input, $nr - 1);
            $current = Token::item($input, $nr);
            $next = Token::item($input, $nr + 1);
            if(!is_array($record)){
                continue;
            }
//            d($record);
            if(
                array_key_exists('is_single_quoted', $record) &&
                array_key_exists('execute', $record) &&
                $record['is_single_quoted'] === true
            ){
                $value .= $record['value'];
            }
            elseif(
                array_key_exists('type', $record) &&
                $record['type'] === 'integer'
            ){
                $value .= $record['execute'];
            }
            elseif(
                array_key_exists('type', $record) &&
                $record['type'] === 'float'
            ){
                $value .= $record['execute'];
            }
            elseif(
                array_key_exists('is_boolean', $record) &&
                $record['is_boolean'] === true
            ){
                if($record['execute'] === true){
                    $value .= 'true';
                } else {
                    $value .= 'false';
                }
            }
            elseif(
                array_key_exists('type', $record) &&
                $record['type'] === 'cast'
            ){
                if($record['cast'] === 'clone'){
                    $value = mb_substr($value, 0, -2) . ' ' . $record['cast'] . ' ';
                    $is_clone = true;
                } else {
                    $value = mb_substr($value, 0, -1) . ' ' . $record['cast'];
                }
                $is_cast = true;
            }
            elseif(
                array_key_exists('is_hex', $record) &&
                $record['is_hex'] === true
            ) {
                $value .= $record['execute'];
            }
            elseif(
                array_key_exists('type', $record) &&
                $record['type'] === 'symbol'
            ){
                if(
                    $is_double_quote === false &&
                    in_array(
                        $record['value'],
                        [
                            '[',
                            ']',
                            '(',
                            ')',
                            ',',
                        ],
                        true
                    )
                ){
                    if(
                        in_array(
                            $record['value'],
                            [
                                ']',
                                ')',
                            ],
                            true
                        )
                    ){
                        if($is_cast){
                            if($is_clone){
                                $is_clone = false;
                            } else {
                                $value .= ' ' . $record['value'] . PHP_EOL;
                            }
                            $is_cast = false;
                        }
                        elseif($is_set){
                            $is_set = false;
                            //nothing
                        } else {
                            $value .= PHP_EOL . $record['value'];
                        }
                    }
                    elseif($record['value'] === '('){
//                        $value .= '$this->value_set(' . PHP_EOL;
                        $value .= $record['value'] . PHP_EOL;
                    } else {
                        $value .= $record['value'] . PHP_EOL;
                    }
                    $is_static_class_call = false;
                }
                elseif(
                    $is_double_quote === false &&
                    in_array(
                        $record['value'],
                        [
                            '=>',
                        ],
                        true
                    )
                ){
                    if($next === '['){
                        $value .= ' ' . $record['value'] . PHP_EOL; //end must be a PHP_EOL
                    } else {
                        $value .= ' ' . $record['value'] . ' ';
                    }
                }
                elseif(
                    $is_double_quote === false &&
                    in_array(
                        $record['value'],
                        [
                            '::',
                        ],
                        true
                    )
                ){
                    $is_static_class_call = true;
                    $explode = explode(':', $value);
                    if(array_key_exists(1, $explode)){
                        $value = '\\' . implode('\\', $explode) . $record['value'];
                    } else {
                        $value .= $record['value'];
                    }
                }
                elseif(
                    $is_static_class_call === true &&
                    $record['value'] === '.'
                ){
                    $value .= '_';
                }
                elseif(
                    in_array(
                        $record['value'],
                        [
                            '\\',
                            '"',
                            '\'',
                            '{{',
                            '}}'
                        ],
                        true
                    )
                ){
                    if(
                        $record['value'] === '"' &&
                        $is_double_quote === false
                    ){
                        $is_double_quote = true;
                        $double_quote_previous = $previous;
                    }
                    elseif(
                        $record['value'] === '"' &&
                        $is_double_quote === true
                    ){
                        $is_double_quote = false;
                        $double_quote_previous = $previous;
                    }
                    if(
                        in_array(
                            $record['value'],
                            [
                                '{{',
                                '}}'
                            ],
                            true
                        )
                    ){
                        if($record['value'] === '{{'){
                            $is_single_line = true;
                        } else {
                            $is_single_line = false;
                        }
                        $value .= mb_substr($record['value'], 0, 1);
                    } else {
                        $value .= $record['value'];
                    }
                }
                elseif(
                    in_array(
                        $record['value'],
                        [
                            '=',
                            '+=',
                            '-=',
                            '*=',
                            '.=',
                            '++',
                            '--',
                            '**',
                        ],
                        true
                    )
                ){
                    $previous = $input['array'][$nr - 1] ?? null;
                    if(
                        $previous &&
                        array_key_exists('type', $previous) &&
                        $previous['type'] === 'variable' &&
                        array_key_exists('name', $previous)
                    ){
                        switch($record['value']){
                            case '.=':
                                $assign = Compile::value_right(
                                    $object,
                                    $flags,
                                    $options,
                                    $input,
                                    $nr,
                                    $next,
                                    $skip
                                );
                                $assign = Compile::value($object, $flags, $options, $tag, $assign, $is_set);
                                $value .= '$data->set(\'' . $previous['name'] . '\', value_concatenate($data->data(\'' . $previous['name'] .'\', ' .  $assign . ')';
                                break;
                            case '+=':
                                $assign = Compile::value_right(
                                    $object,
                                    $flags,
                                    $options,
                                    $input,
                                    $nr,
                                    $next,
                                    $skip
                                );
                                $assign = Compile::value($object, $flags, $options, $tag, $assign, $is_set);
                                $value .= '$data->set(\'' . $previous['name'] . '\', value_plus($data->data(\'' . $previous['name'] .'\', ' .  $assign . ')';
                                break;
                            case '-=':
                                $assign = Compile::value_right(
                                    $object,
                                    $flags,
                                    $options,
                                    $input,
                                    $nr,
                                    $next,
                                    $skip
                                );
                                $assign = Compile::value($object, $flags, $options, $tag, $assign, $is_set);
                                $value .= '$data->set(\'' . $previous['name'] . '\', value_minus($data->data(\'' . $previous['name'] .'\', ' .  $assign . ')';
                                break;
                            case '*=':
                                $assign = Compile::value_right(
                                    $object,
                                    $flags,
                                    $options,
                                    $input,
                                    $nr,
                                    $next,
                                    $skip
                                );
                                $assign = Compile::value($object, $flags, $options, $tag, $assign, $is_set);
                                $value .= '$data->set(\'' . $previous['name'] . '\', value_multiply($data->data(\'' . $previous['name'] .'\', ' .  $assign . ')';
                                break;
                            case '=':
                                $assign = Compile::value_right(
                                    $object,
                                    $flags,
                                    $options,
                                    $input,
                                    $nr,
                                    $next,
                                    $skip
                                );
                                $assign = Compile::value($object, $flags, $options, $tag, $assign, $is_set);
                                $value .= '$data->set(\'' . $previous['name'] . '\', ' .  $assign . ')';
                                break;
                            case '++' :
                                $value = '$data->set(\'' . $previous['name'] . '\', ' .  '$this->value_plus_plus($data->data(\'' . $previous['name'] . '\')))';
                                break;
                            case '--' :
                                $value = '$data->set(\'' . $previous['name'] . '\', ' .  '$this->value_minus_minus($data->data(\'' . $previous['name'] . '\')))';
                                break;
                            case '**' :
                                $value = '$data->set(\'' . $previous['name'] . '\', ' .  '$this->value_multiply_multiply($data->data(\'' . $previous['name'] . '\')))';
                                break;
                        }
                    } else {
                        if(
                            array_key_exists('is_multiline', $record) &&
                            $record['is_multiline'] === true
                        ){
                            throw new TemplateException(
                                $record['tag'] .
                                PHP_EOL .
                                'Invalid argument for {{' . $record['value'] . '}}' .
                                PHP_EOL .
                                'On line: ' .
                                $record['line']['start']  .
                                ', column: ' .
                                $record['column'][$record['line']['start']]['start'] .
                                ' in source: '.
                                $source .
                                '.'
                            );
                        } else {
                            throw new TemplateException(
                                $record['tag'] .
                                PHP_EOL .
                                'Invalid argument for {{' . $record['value'] . '}}' .
                                PHP_EOL .
                                'On line: ' .
                                $record['line']  .
                                ', column: ' .
                                $record['column']['start'] .
                                ' in source: ' .
                                $source .
                                '.'
                            );
                        }
                    }
                }
                elseif(
                    in_array(
                        $record['value'],
                        [
                            '+',
                            '-',
                            '*',
                            '/',
                            '%',
                            '.',
                            '<',
                            '<=',
                            '<<',
                            '>',
                            '>=',
                            '>>',
                            '==',
                            '===',
                            '!=',
                            '!==',
                            '.=',
                            '+=',
                            '-=',
                            '*=',
                            '...',
                            '=>',
                            '&&',
                            '||',
                            'xor',
                            '??',
                            'and',
                            'or'
                        ],
                        true
                    )
                ){
                    $right = Compile::value_right(
                        $object,
                        $flags,
                        $options,
                        $input,
                        $nr,
                        $next,
                        $skip
                    );
                    $right = Compile::value($object, $flags, $options, $tag, $right, $is_set, $before, $after);
                    if(array_key_exists('value', $record)){
                        $value = Compile::value_calculate($object, $flags, $options, $record['value'], $value, $right);
                    }
                }
                else {
                    $value .= $record['value'];
                }
            }
            elseif(
                array_key_exists('value', $record) &&
                in_array(
                    $record['value'],
                    [
                        '{{',
                        '}}'
                    ],
                    true
                )
            ){
                if(
                    $is_double_quote === true &&
                    $record['value'] === '{{'
                ){
                    if($double_quote_previous === '\\'){
                        $value .= '\\" . ';
                    } else {
                        $value .= '" . ';
                    }
                    $double_quote_previous = false;
                }
                elseif(
                    $is_double_quote === true &&
                    $record['value'] === '}}'
                ){
                    if($double_quote_previous === '\\'){
                        $value .= ' . \\"';
                    } else {
                        $value .= ' . "';
                    }
                    $double_quote_previous = false;
                } else {
                    //nothing
                }
            }
            elseif(
                array_key_exists('is_null', $record) &&
                $record['is_null'] === true
            ){
                $value .= 'NULL';
            }
            elseif(
                array_key_exists('type', $record) &&
                $record['type'] === 'string'
            ){
                $possible_variable = $input['array'][$nr + 1] ?? null;
                if(
                    $possible_variable &&
                    array_key_exists('type', $possible_variable) &&
                    $possible_variable['type'] === 'variable' &&
                    $record['execute'] === 'as'
                ){
                    $value .=  ' ' . $record['execute'] . ' ';
                } else {
                    $value .=  $record['execute'];
                }
            }
            elseif(
                array_key_exists('type', $record) &&
                $record['type'] === 'array'
            ){
                $array_value = Compile::value($object, $flags, $options, $tag, $record, $is_set);
//                d($array_value);
                $data = Compile::string_array($array_value);
                foreach($data as $nr => $line){
                    $char = trim($line);
                    if($char === '['){
                        $data[$nr] = $line;
                    }
                    elseif(
                        in_array(
                            $char,
                            [
                                ']',
                                '],'
                            ], true
                        )
                    ){
                        $data[$nr] = $line;
                    } else {
                        $data[$nr] = $line;
                    }
                }
                $value .= implode(PHP_EOL, $data);
            }
            elseif(
                array_key_exists('type', $record) &&
                $record['type'] === 'set'
            ){
                $set_value = '$this->value_set(' . PHP_EOL;
                $set_value .= Compile::value($object, $flags, $options, $tag, $record, $is_set) . PHP_EOL;
                $set_value .= ')';
                $value .= $set_value;
            }
            elseif(
                array_key_exists('type', $record) &&
                $record['type'] === 'method'
            ){
                if(
                    array_key_exists('is_class_method', $record['method']) &&
                    $record['method']['is_class_method'] === true
                ){
                    $explode = explode(':', $record['method']['class']);
                    if(array_key_exists(1, $explode)){
                        $class = '\\' . implode('\\', $explode);
                    } else {
                        $class_static = Compile::class_static($object);
                        $class = $record['method']['class'];
                        if(
                            !in_array(
                                $class,
                                $class_static,
                                true
                            )
                        ) {
                            throw new Exception('Invalid class: ' . $class . ', available classes: ' . PHP_EOL . implode(PHP_EOL, $class_static));
                        }
                    }
                    $method_value = $class .
                        $record['method']['call_type'] .
                        str_replace('.', '_', $record['method']['name']) .
                        '(';
                    $method_value .= Compile::argument($object, $flags, $options, $record, $before, $after);
                    $method_value .= ')';
                } else {
                    $plugin = Compile::plugin($object, $flags, $options, $tag, str_replace('.', '_', $record['method']['name']));
                    $method_value = $plugin . '(' . PHP_EOL;
                    $method_value .= Compile::argument($object, $flags, $options, $record, $before, $after);
                    $method_value .= ')';
                }
                $value .= $method_value;
            }
            elseif(
                array_key_exists('type', $record) &&
                $record['type'] === 'variable_method'
            ){
                $modifier_value = '';
                if(array_key_exists('modifier', $record)){
                    $previous_modifier = '$data->data(\'' . $record['name'] . '\')';
                    //add method and arguments

                    foreach($record['modifier'] as $modifier_nr => $modifier){
                        $plugin = Compile::plugin($object, $flags, $options, $tag, str_replace('.', '_', $modifier['name']));
                        if($is_single_line){
                            $modifier_value = $plugin . '( ' ;
                            $modifier_value .= $previous_modifier . ', ';
                        } else {
                            $modifier_value = $plugin . '(';
                            $modifier_value .= $previous_modifier . ', ';
                        }
                        $is_argument = false;
                        if(array_key_exists('argument', $modifier)){
                            foreach($modifier['argument'] as $argument_nr => $argument){
                                if($is_single_line){
                                    $argument = Compile::value($object, $flags, $options, $tag, $argument, $is_set, $before, $after);
                                    if($argument !== ''){
                                        $modifier_value .= $argument . ', ';
                                        $is_argument = true;
                                    }
                                } else {
                                    $argument = Compile::value($object, $flags, $options, $tag, $argument, $is_set, $before, $after);
                                    if($argument !== '') {
                                        $modifier_value .= $argument . ', ';
                                        $is_argument = true;
                                    }
                                }
                            }
                            if($is_argument === true){
                                if($is_single_line){
                                    $modifier_value = mb_substr($modifier_value, 0, -2);
                                } else {
                                    $modifier_value = mb_substr($modifier_value, 0, -2);
                                }
                            } else {
                                $modifier_value = mb_substr($modifier_value, 0, -1);
                            }
                        }
                        $modifier_value .= ')';
                        $previous_modifier = $modifier_value;
                    }
                    $value .= $modifier_value;
                    $is_single_line = false;
                } else {
                    $plugin = str_replace('.', '_', $record['method']['name']);
                    //call_type = :: or ->
                    $call_type = $record['method']['call_type'];
                    if(array_key_exists('variable', $record)){
                        $call_type = '->';
                    }
                    $method_value = $call_type . $plugin . '(';
                    if(
                        array_key_exists('method', $record) &&
                        array_key_exists('argument', $record['method'])
                    ){
                        $is_argument = false;
                        foreach($record['method']['argument'] as $argument_nr => $argument){
                            $argument = Compile::value($object, $flags, $options, $tag, $argument, $is_set, $before, $after);
                            if($argument !== ''){
                                $method_value .= $argument . ', ';
                                $is_argument = true;
                            }
                        }
                        if($is_argument === true){
                            $method_value = mb_substr($method_value, 0, -2);
                            $method_value .= ')';
                        } else {
                            $method_value .= ')';
                        }
                    }
                    $value .= '$data->data(\'' . $record['variable']['name'] . '\')' . $method_value;
                }
            }
            elseif(
                array_key_exists('type', $record) &&
                $record['type'] === 'variable'
            ){
                if(
                    array_key_exists('variable', $record) &&
                    array_key_exists('is_assign', $record['variable']) &&
                    $record['variable']['is_assign'] === true
                ){
                    //assign
                    switch($record['variable']['operator']){
                        case '=':
                            $variable_value = Compile::value($object, $flags, $options, $tag, $record['variable']['value'], $is_set);
                            $value .= '$data->set(\'' . $record['variable']['name'] . '\', ' . $variable_value . ')';
                            break;
                        case '.=':
                            $variable_value = Compile::value($object, $flags, $options, $tag, $record['variable']['value'], $is_set);
                            $value .= '$data->set(\'' . $record['variable']['name'] . '\', ' .  '$this->value_concatenate($data->data(\'' . $record['variable']['name'] . '\'), ' .  $variable_value . '))';
                            break;
                        case '+=':
                            $variable_value = Compile::value($object, $flags, $options, $tag, $record['variable']['value'], $is_set);
                            $value .= '$data->set(\'' . $record['variable']['name'] . '\', ' .  '$this->value_plus($data->data(\'' . $record['variable']['name'] . '\'), ' .  $variable_value . '))';
                            break;
                        case '-=':
                            $variable_value = Compile::value($object, $flags, $options, $tag, $record['variable']['value'], $is_set);
                            $value .= '$data->set(\'' . $record['variable']['name'] . '\', ' .  '$this->value_minus($data->data(\'' . $record['variable']['name'] . '\'), ' .  $variable_value . '))';
                            break;
                        case '*=':
                            $variable_value = Compile::value($object, $flags, $options, $tag, $record['variable']['value'], $is_set);
                            $value .= '$data->set(\'' . $record['variable']['name'] . '\', ' .  '$this->value_multiply($data->data(\'' . $record['variable']['name'] . '\'), ' .  $variable_value . '))';
                            break;
                        case '++':
                            $value .= '$data->set(\'' . $record['variable']['name'] . '\', ' .  '$this->value_plus_plus($data->data(\'' . $record['variable']['name'] . '\')))';
                            break;
                        case '--':
                            $value .= '$data->set(\'' . $record['variable']['name'] . '\', ' .  '$this->value_minus_minus($data->data(\'' . $record['variable']['name'] . '\')))';
                            break;
                        case '**':
                            $value .= '$data->set(\'' . $record['variable']['name'] . '\', ' .  '$this->value_multiply_multiply($data->data(\'' . $record['variable']['name'] . '\')))';
                            break;
                        default:
                            breakpoint($record);
                            throw new Exception('Not implemented...');
                    }
                } else {
                    $modifier_value = '';
                    if(array_key_exists('modifier', $record)){
                        $previous_modifier = '$data->data(\'' . $record['name'] . '\')';
                        $after[] = [
                            'attribute' => $record['name']
                        ];
                        foreach($record['modifier'] as $modifier_nr => $modifier){
                            $plugin = Compile::plugin($object, $flags, $options, $tag, str_replace('.', '_', $modifier['name']));
                            if($is_single_line){
                                $modifier_value = $plugin . '(';
                                $modifier_value .= $previous_modifier . ', ';
                            } else {
                                $modifier_value = $plugin . '(';
                                $modifier_value .= $previous_modifier . ', ';
                            }
                            $is_argument = false;
                            if(array_key_exists('argument', $modifier)){
                                foreach($modifier['argument'] as $argument_nr => $argument){
                                    if($is_single_line){
                                        $argument = Compile::value($object, $flags, $options, $tag, $argument, $is_set, $before, $after);
                                        if($argument !== ''){
                                            $modifier_value .= $argument . ', ';
                                            $is_argument = true;
                                        }
                                    } else {
                                        $argument = Compile::value($object, $flags, $options, $tag, $argument, $is_set, $before, $after);
                                        if($argument !== '') {
                                            $modifier_value .= $argument . ', ';
                                            $is_argument = true;
                                        }
                                    }
                                }
                                if($is_argument === true){
                                    if($is_single_line){
                                        $modifier_value = mb_substr($modifier_value, 0, -2);
                                    } else {
                                        $modifier_value = mb_substr($modifier_value, 0, -2);
                                    }
                                } else {
                                    $modifier_value = mb_substr($modifier_value, 0, -1);
                                }
                            }
                            $modifier_value .= ')';
                            $previous_modifier = $modifier_value;
                        }
                        $value .= $modifier_value;
                        $is_single_line = false;
                    } else {
                        if(
                            array_key_exists('array_notation', $record) && !empty($record['array_notation']) &&
                            array_key_exists('array', $record['array_notation']) && !empty($record['array_notation']['array']) &&
                            array_key_exists('array', $record['array_notation']['array'][0]) && !empty($record['array_notation']['array'][0]['array'])
                        ){
                            $uuid_variable = Core::uuid_variable();
                            $before[] =  $uuid_variable . ' = $data->data(\'' . $record['name'] . '\');';
                            $before[] = 'if(is_array(' . $uuid_variable . ')){';
                            $bracket = 0;
                            $collect = [];
                            $collect['array'] = [];
                            foreach($record['array_notation']['array'][0]['array'] as $array_notation_nr => $array_notation){
                                if(
                                    array_key_exists('value', $array_notation) &&
                                    $array_notation['value'] == '['
                                ){
                                    $bracket++;
                                    continue;
                                    //need $data[12] for array and $data->data('name') for object
                                }
                                if(
                                    array_key_exists('value', $array_notation) &&
                                    $array_notation['value'] == ']'
                                ){
                                    $bracket--;
                                    if($bracket === 0){
                                        $collect = Compile::value($object, $flags, $options, $tag, $collect, $is_set, $before, $after);
                                        $before[] = $uuid_variable . ' = ' . $uuid_variable . '[' . $collect .  '] ?? null;';
                                        $collect = [];
                                    }
                                    continue;
                                }
                                if($bracket >= 1){
                                    $collect['array'][] = $array_notation;
                                }
                            }
                            $before[] = '}';
                            $value = $uuid_variable;
                            $after[] = [
                                'attribute' => $uuid_variable
                            ];
                        } else {
                            $value .= '$data->data(\'' . $record['name'] . '\')';
                            $after[] = [
                                'attribute' => $record['name']
                            ];
                        }
                    }
                }
            }
            elseif(
                array_key_exists('type', $record) &&
                $record['type'] === 'whitespace' &&
                $is_double_quote === true
            ){
                $value .=  $record['value'];
            }
            elseif(
                array_key_exists('type', $record) &&
                $record['type'] === 'whitespace' &&
                $is_double_quote === false
            ){
                //nothing
            } else {
                $right = Compile::value_right(
                    $object,
                    $flags,
                    $options,
                    $input,
                    $nr,
                    $next,
                    $skip
                );
                $right = Compile::value($object, $flags, $options, $tag, $right, $is_set, $before, $after);
                if(array_key_exists('value', $record)){
                    $value = Compile::value_calculate($object, $flags, $options, $record['value'], $value, $right);
                }
            }
        }
        return $value;
    }

    public static function value_single_quote(App $object, $flags, $options, $input): array
    {
        if(!array_key_exists('array', $input)){
            return $input;
        }
        $is_single_quote = false;
        foreach($input['array'] as $nr => $record){
            $current = Token::item($input, $nr);
            $next = Token::item($input, $nr + 1);
            if(
                $current === '\''  &&
                $is_single_quote === false
            ){
                $is_single_quote = $nr;
            }
            elseif(
                $current === '\''  &&
                $is_single_quote !== false
            ){
                for($i = $is_single_quote + 1; $i <= $nr; $i++){
                    $current = Token::item($input, $i);
                    $input['array'][$is_single_quote]['value'] .= $current;
                    $input['array'][$i] = null;
                }
                $input['array'][$is_single_quote]['type'] = 'string';
                $input['array'][$is_single_quote]['execute'] = substr($input['array'][$is_single_quote]['value'], 1, -1);
                $input['array'][$is_single_quote]['is_single_quoted'] = true;
                $is_single_quote = false;
            }
        }
        $input = Token::cleanup($object, $flags, $options, $input);
        return $input;
    }

    public static function value_set(App $object, $flags, $options, $input, &$is_set=false): array
    {
//        d($input);
        if(!array_key_exists('array', $input)){
            return $input;
        }
        $count = count($input['array']);
        $first = reset($input['array']);
        if(
            $first !== false &&
            array_key_exists('value', $first) &&
            $first['value'] === '('
        ){
            $set = [];
            $set['type'] = 'set';
            $set['value'] = '(';
            $set['array'] = [];
            $set_depth = 1;
            $after = null;
            for($i = 1; $i <= $count - 1; $i++){
                $current = Token::item($input, $i);
                if($current === '('){
                    $set_depth++;
                }
                elseif($current === ')'){
                    $set_depth--;
                    if($set_depth === 0){
                        $after = [];
                    }
                }
                elseif($after !== null){
                    $after[] = $input['array'][$i];
                }
                elseif(
                    in_array(
                        $current,
                        [
                            'array',
                            'bool',
                            'boolean',
                            'int',
                            'integer',
                            'float',
                            'double',
                            'string',
                            'object',
                            'clone'
                        ],
                        true
                    )
                ){
                    $is_set = false;
                    return $input;
                } else {
                    $set['value'] .= $current;
                    $set['array'][] = $input['array'][$i];
                }
            }
            $set['value'] .= ')';
            if($after !== null){
                $input['array'] = [
                    0 => $set,
                ];
                foreach($after as $item){
                    $input['array'][] = $item;
                }
            } else {
                $input['array'] = [
                    0 => $set,
                ];
            }
            $is_set = true;
        }
        return $input;
    }

    public static function value_calculate(App $object, $flags, $options, $current, $left, $right): string
    {
        $value = '';
        switch($current){
            case 'true':
            case 'false':
            case 'null':
                $value = $current;
                break;
            case '.=':
            case '.':
                $value = '$this->value_concatenate(' .$left . ', ' .$right . ')';
                break;
            case '+':
                $value = '$this->value_plus(' .$left . ', ' .$right . ')';
                break;
            case '-':
                $value = '$this->value_minus(' .$left . ', ' .$right . ')';
                break;
            case '*':
                $value = '$this->value_multiply(' .$left . ', ' .$right . ')';
                break;
            case '%':
                $value = '$this->value_modulo(' .$left . ', ' .$right . ')';
                break;
            case '/':
                $value = '$this->value_divide(' .$left . ', ' .$right . ')';
                break;
            case '<':
                $value = '$this->value_smaller(' . $left . ', ' .$right . ')';
                break;
            case '<=':
                $value = '$this->value_smaller_equal(' .$left . ', ' .$right . ')';
                break;
            case '<<':
                $value = '$this->value_smaller_smaller(' .$left . ', ' .$right . ')';
                break;
            case '>':
                $value = '$this->value_greater(' .$left . ', ' .$right . ')';
                break;
            case '>=':
                $value = '$this->value_greater_equal(' .$left . ', ' .$right . ')';
                break;
            case '>>':
                $value = '$this->value_greater_greater(' .$left . ', ' .$right . ')';
                break;
            case '==':
                $value = '$this->value_equal(' .$left . ', ' .$right . ')';
                break;
            case '===':
                $value = '$this->value_identical(' .$left . ', ' .$right . ')';
                break;
            case '!=':
            case '<>':
                $value = '$this->value_not_equal(' .$left . ', ' .$right . ')';
                break;
            case '!==':
                $value = '$this->value_not_identical(' .$left . ', ' .$right . ')';
                break;
            case '??':
                $value = $left . ' ?? ' . $right;
                break;
            case '&&':
            case 'and' :
                $value = $left . ' && ' . $right;
                break;
            case '||':
            case 'or':
                $value = $left . ' || ' . $right;
                break;
            case 'xor':
                $value = $left . ' xor ' . $right;
                break;
        }
        return $value;
    }

    /**
     * @throws Exception
     */
    public static function value_right(App $object, $flags, $options, $input, $nr, $next, &$skip=0): array
    {
        $count = count($input['array']);
        $right = '';
        $right_array = [];
        switch($next){
            case '(':
                $set_depth = 1;
                $right = $next;
                $right_array[] = $input['array'][$nr + 1];
                for($i = $nr + 2; $i < $count; $i++){
                    if(!array_key_exists($i, $input['array'])){
                        continue;
                    }
                    $previous = Token::item($input, $i - 1);
                    $item = Token::item($input, $i);
                    if($item === '('){
                        $set_depth++;
                    }
                    elseif($item === ')'){
                        $set_depth--;
                    }
                    if(
                        $item === ')' &&
                        $set_depth === 0 &&
                        $i > ($nr + 1)
                    ){
                        $right .= $item;
                        $right_array[] = $input['array'][$i];
                        $skip++;
                        break;
                    }
                    $right .= $item;
                    $right_array[] = $input['array'][$i];
                    $skip++;
                }
                break;
            case '\'':
                for($i = $nr + 1; $i < $count; $i++){
                    if(!array_key_exists($i, $input['array'])){
                        continue;
                    }
                    $previous = Token::item($input, $i - 1);
                    $item = Token::item($input, $i);
                    if(
                        $item === '\'' &&
                        $previous !== '\\' &&
                        $i > ($nr + 1)
                    ){
                        $right .= $item;
                        $right_array[] = $input['array'][$i];
                        $skip++;
                        break;
                    }
                    $right .= $item;
                    $right_array[] = $input['array'][$i];
                    $skip++;
                }
                break;
            case '"':
                for($i = $nr + 1; $i < $count; $i++){
                    if(!array_key_exists($i, $input['array'])){
                        continue;
                    }
                    $previous = Token::item($input, $i - 1);
                    $item = Token::item($input, $i);
                    if(
                        $item === '"' &&
                        $previous !== '\\' &&
                        $i > ($nr + 1)
                    ){
                        $right .= $item;
                        $right_array[] = $input['array'][$i];
                        $skip++;
                        break;
                    }
                    $right .= $item;
                    $right_array[] = $input['array'][$i];
                    $skip++;
                }
                break;
            /*
        case NULL:
            $right = 'NULL';
            $right_array[] = [
                'value' => $right,
                'execute' => NULL,
                'is_null' => true
            ];
            $skip++;
        break;
            */
            case '=':
                $skip++;
                for($i = $nr + 2; $i < $count; $i++){
                    if(!array_key_exists($i, $input['array'])){
                        continue;
                    }
                    $previous = Token::item($input, $i - 1);
                    $item = Token::item($input, $i);
                    if(
                        in_array(
                            $item,
                            [
                                ',',
                                '.',
                                '+',
                                '-',
                                '*',
                                '%',
                                '/',
                                '=',
                                '<',
                                '(',
                                ')',
                                '<=',
                                '<<',
                                '>',
                                '>=',
                                '>>',
                                '==',
                                '===',
                                '!=',
                                '!==',
                                '??',
                                '&&',
                                '||',
                                '.=',
                                '+=',
                                '-=',
                                '*=',
                                '...',
                                '=>',
                                '++',
                                '--',
                                '**',
                                'and',
                                'or',
                                'xor'
                            ],
                            true
                        )
                    ){
                        break;
                    }
                    $right .= $item;
                    $right_array[] = $input['array'][$i];
                    $skip++;
                }
                break;
            default:
                for($i = $nr + 1; $i < $count; $i++){
                    if(!array_key_exists($i, $input['array'])){
                        continue;
                    }
                    $previous = Token::item($input, $i - 1);
                    $item = Token::item($input, $i);
                    if(
                        in_array(
                            $item,
                            [
                                ',',
                                '.',
                                '+',
                                '-',
                                '*',
                                '%',
                                '/',
                                '=',
                                '<',
                                '(',
                                ')',
                                '<=',
                                '<<',
                                '>',
                                '>=',
                                '>>',
                                '==',
                                '===',
                                '!=',
                                '!==',
                                '??',
                                '&&',
                                '||',
                                '.=',
                                '+=',
                                '-=',
                                '*=',
                                '...',
                                '=>',
                                '++',
                                '--',
                                '**',
                                'and',
                                'or',
                                'xor'
                            ],
                            true
                        )
                    ){
                        break;
                    }
                    $right .= $item;
                    $right_array[] = $input['array'][$i];
                    $skip++;
                }
                break;
        }
        return [
            'string' => $right,
            'array' => $right_array
        ];
    }

    public static function string_array($string=''): array
    {
        $data = mb_str_split($string);
        $is_single_quote = false;
        $is_double_quote = false;
        $line = 0;
        $list = [];
        foreach($data as $nr => $char){
            $previous = $data[$nr - 1] ?? null;
            if(
                $previous !== '\\' &&
                $char === '\''
            ){
                if($is_single_quote === false){
                    $is_single_quote = true;
                } else {
                    $is_single_quote = false;
                }
            }
            elseif(
                $previous !== '\\' &&
                $char === '"'
            ){
                if($is_double_quote === false){
                    $is_double_quote = true;
                } else {
                    $is_double_quote = false;
                }
            }
            if(
                $is_single_quote === false &&
                $is_double_quote === false &&
                $char === PHP_EOL
            ){
                $line++;
            } else {
                if(!array_key_exists($line, $list)){
                    $list[$line] = '';
                }
                $list[$line] .= $char;
            }
        }
        return $list;
    }
}