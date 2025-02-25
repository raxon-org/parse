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


    public static function data_tag(App $object, $flags, $options ,$tags=[]): array
    {
        $data = [];
        foreach($tags as $row_nr => $list){
            foreach($list as $nr => $record){
                if(array_key_exists('method', $record) && array_key_exists('name', $record['method'])){
                    $method = $record['method']['name'] . '(';
                    foreach($record['method']['argument'] as $argument_nr => $argument){
                        $method_value .= Compile::argument($object, $flags, $options, $record);
                    }
                    $method .= ')';
                    ddd($method);
                }
                d($record);
            }
        }
        return $data;
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
}