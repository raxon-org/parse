<?php
namespace Package\Raxon\Parse\Service;

use Raxon\App;

use Raxon\Module\Autoload;
use Raxon\Module\Core;
use Raxon\Module\File;

use Plugin;
use Exception;
use ReflectionClass;

use Raxon\Exception\LocateException;
use Raxon\Exception\TemplateException;

class Build
{
    use Plugin\Format_code;
    use Plugin\Basic;

    public function __construct(App $object, $flags, $options){
        $this->object($object);
        $this->flags($flags);
        $this->options($options);
    }

    /**
     * @throws Exception
     * @throws LocateException
     * @throws TemplateException
     */
    public static function create(App $object, $flags, $options, $tags=[]): array
    {
        $options->class = $options->class ?? 'Main';
        Build::document_default($object, $flags, $options);
        $data = Build::document_tag($object, $flags, $options, $tags);
        $document = Build::document_header($object, $flags, $options);
        $document = Build::document_use($object, $flags, $options, $document, 'package.raxon/parse.build.use.class');
        $document[] = '';
        $document[] = 'class '. $options->class .' {';
        $document[] = '';
        $object->config('package.raxon/parse.build.state.indent', 1);
        //indent++
        $document = Build::document_use($object, $flags, $options, $document, 'package.raxon/parse.build.use.trait');
        $document[] = '';
        $document = Build::document_construct($object, $flags, $options, $document);
        $document[] = '';
        $document = Build::document_run($object, $flags, $options, $document, $data);
        $document[] = '}';
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

    public static function class_static(App $object): array
    {
        $use_class = $object->config('package.raxon/parse.build.use.class');
        foreach($use_class as $use_class_nr => $use_class_record){
            $explode = explode('as', $use_class_record);
            if(array_key_exists(1, $explode)){
                $use_class[$use_class_nr] = trim($explode[1]);
            } else {
                $temp = explode('\\', $explode[0]);
                $use_class[$use_class_nr] = array_pop($temp);
            }
            $use_class[$use_class_nr] .= '::';
        }
        return $use_class;
    }

    /**
     * @throws Exception
     * @throws LocateException
     * @throws TemplateException
     */
    public static function document_tag(App $object, $flags, $options, $tags = []): array
    {
        $source = $options->source ?? '';
        $data = [];
        $variable_assign_next_tag = false;
        $for = [];
        $foreach = [];
        $while = [];
        $if = [];
        $is_block = false;
        $is_literal = false;
        $is_literal_block = false;
        $block = [];
        $break_level = 0;
        $object->config('package.raxon/parse.build.state.break.level', $break_level);
        $data[] = '$object->config(\'package.raxon/parse.build.state.source.url\', \''. str_replace('\'', '\\\'', $source) .'\');';
        foreach($tags as $row_nr => $list){
            foreach($list as $nr => &$record){
                if(
                    array_key_exists('marker', $record) &&
                    array_key_exists('value', $record['marker']) &&
                    array_key_exists('array', $record['marker']['value']) &&
                    empty($record['marker']['value']['array'])
                ){
                    unset($tags[$row_nr][$nr]);
                    continue;
                }
                $tag = [
                    'tag' => $record['tag'] ?? $record['execute'] ?? $record['value'] ?? null,
                    'line' => $record['line'] ?? null,
                    'column' => $record['column'] ?? null,
                ];
                if($tag['tag'] !== null){
                    $data[] = '$object->config(\'package.raxon/parse.build.state.tag\', Core::object(\'' . Core::object($tag, Core::TRANSFER) .'\', Core::FINALIZE));';
                }
                if(
                    $is_literal === true ||
                    $is_literal_block === true
                ){
                    if(
                        array_key_exists('marker', $record) &&
                        array_key_exists('name', $record['marker']) &&
                        array_key_exists('is_close', $record['marker']) &&
                        $record['marker']['is_close'] === true &&
                        $record['marker']['name'] === 'literal'
                    ){
                        $is_literal = false;
                        continue;
                    }
                    elseif(
                        array_key_exists('marker', $record) &&
                        array_key_exists('name', $record['marker']) &&
                        array_key_exists('is_close', $record['marker']) &&
                        $record['marker']['name'] === 'block'
                    ){
                        $is_literal_block = false;
                    } else {
                        if($is_block){
                            if(array_key_exists('tag', $record)){
                                $block[] = 'echo \'' . str_replace('\'', '\\\'', $record['tag']) . '\';';
                            }
                            elseif(array_key_exists('text', $record)){
                                $block[] = 'echo \'' . str_replace('\'', '\\\'', $record['text']) . '\';';
                            } else {
                                ddd($record);
                            }
                        } else {
                            if(array_key_exists('tag', $record)){
                                $data[] = 'echo \'' . str_replace('\'', '\\\'', $record['tag']) . '\';';
                            }
                            elseif(array_key_exists('text', $record)){
                                $data[] = 'echo \'' . str_replace('\'', '\\\'', $record['text']) . '\';';
                            } else {
                                ddd($record);
                            }
                        }
                        continue;
                    }
                }
                $text = Build::text($object, $flags, $options, $record, $variable_assign_next_tag);
                if($text){
                    if($is_block){
                        $block[] = $text;
                    } else {
                        $data[] = $text;
                    }
                }
                $variable_assign_next_tag = false; //Build::text is taking care of this
                $variable_assign = Build::variable_assign($object, $flags, $options, $record);
                if($variable_assign){
                    if($is_block){
                        $block[] = $variable_assign;
                    } else {
                        $data[] = $variable_assign;
                    }
                    $next = $list[$nr + 1] ?? false;
                    if($next !== false){
                        $tags[$row_nr][$nr + 1] = Build::variable_assign_next($object, $flags, $options, $record, $next);
                        $list[$nr + 1] = $tags[$row_nr][$nr + 1];
                    } else {
                        $variable_assign_next_tag = true;
                    }
                }
                $variable_define = Build::variable_define($object, $flags, $options, $record);
                if($variable_define){
                    foreach($variable_define as $variable_define_nr => $line){
                        if($is_block){
                            $block[] = $line;
                        } else {
                            $data[] = $line;
                        }
                    }
                }
                $method = Build::method($object, $flags, $options, $record);
                if($method){
                    if(
                        array_key_exists('method', $record) &&
                        array_key_exists('name', $record['method'])
                    ){
                        if(
                            in_array(
                                $record['method']['name'],
                                [
                                    'for.each',
                                    'for_each',
                                    'foreach',
                                ],
                                true
                            )
                        ){
                            $foreach[] = $record;
                            $break_level++;
                            $object->config('package.raxon/parse.build.state.break.level', $break_level);
                        }
                        elseif(
                            in_array(
                                $record['method']['name'],
                                [
                                    'for',
                                ],
                                true
                            )
                        ){
                            $for[] = $record;
                            $break_level++;
                            $object->config('package.raxon/parse.build.state.break.level', $break_level);
                        }
                        elseif(
                            in_array(
                                $record['method']['name'],
                                [
                                    'while',
                                ],
                                true
                            )
                        ){
                            $while[] = $record;
                            $break_level++;
                            $object->config('package.raxon/parse.build.state.break.level', $break_level);
                        }
                        elseif(
                            in_array(
                                $record['method']['name'],
                                [
                                    'if',
                                ],
                                true
                            )
                        ){
                            $if[] = $record;
                        }
                        elseif(
                            in_array(
                                $record['method']['name'],
                                [
                                    'block.data',
                                ],
                                true
                            )
                        ){
                            $is_block = true;
                            $is_literal_block = true;
                            continue;
                        }
                    }
                    if($is_block){
                        $block[] = $method;
                    } else {
                        $data[] = $method;
                    }
                    $variable_assign_next_tag = true;
                }
                if(
                    array_key_exists('marker', $record) &&
                    array_key_exists('is_close', $record['marker']) &&
                    $record['marker']['is_close'] === true
                ){
                    $ltrim = $object->config('package.raxon/parse.build.state.ltrim');
                    if($ltrim > 0){
                        $ltrim--;
                        $object->config('package.raxon/parse.build.state.ltrim', $ltrim);
                    }
                    //need to count them by name
                    if(array_key_exists('name', $record['marker'])) {
                        if (
                            in_array(
                                $record['marker']['name'],
                                [
                                    'for.each',
                                    'for_each',
                                    'foreach',
                                ],
                                true
                            )
                        ) {
                            $foreach_reverse = array_reverse($foreach);
                            $has_close = false;
                            foreach ($foreach_reverse as $foreach_nr => &$foreach_record) {
                                if (
                                    array_key_exists('method', $foreach_record) &&
                                    array_key_exists('has_close', $foreach_record['method']) &&
                                    $foreach_record['method']['has_close'] === true
                                ) {
                                    //skip
                                } elseif (
                                    array_key_exists('method', $foreach_record)
                                ) {
                                    $has_close = true;
                                    $foreach_record['method']['has_close'] = true;
                                    if($is_block){
                                        $block[] = '}';
                                    } else {
                                        $data[] = '}';
                                    }
                                    $variable_assign_next_tag = true;
                                    $break_level--;
                                    $object->config('package.raxon/parse.build.state.break.level', $break_level);
                                    break; //only 1 at a time
                                }
                            }
                            if ($has_close === false) {
                                if (
                                    array_key_exists('is_multiline', $record) &&
                                    $record['is_multiline'] === true
                                ) {
                                    throw new TemplateException(
                                        str_replace('\'', '\\\'', $record['tag']) . PHP_EOL .
                                        'Unused foreach close tag "{{/foreach}}" on line: ' .
                                        $record['line']['start'] .
                                        ', column: ' .
                                        $record['column'][$record['line']['start']]['start'] .
                                        ' in source: ' .
                                        $source,
                                    );

                                } else {
                                    throw new TemplateException(
                                        str_replace('\'', '\\\'', $record['tag']) . PHP_EOL .
                                        'Unused foreach close tag "{{/foreach}}" on line: ' .
                                        $record['line'] .
                                        ', column: ' .
                                        $record['column']['start'] .
                                        ' in source: ' .
                                        $source,
                                    );
                                }
                            }
                            $foreach = array_reverse($foreach_reverse);
                        }
                        elseif (
                            in_array(
                                $record['marker']['name'],
                                [
                                    'while',
                                ],
                                true
                            )
                        ) {
                            $while_reverse = array_reverse($while);
                            $has_close = false;
                            foreach ($while_reverse as $while_nr => $while_record) {
                                if (
                                    array_key_exists('method', $while_record) &&
                                    array_key_exists('has_close', $while_record['method']) &&
                                    $while_record['method']['has_close'] === true
                                ) {
                                    //skip
                                } elseif (
                                    array_key_exists('method', $while_record)
                                ) {
                                    $has_close = true;
                                    $while_reverse[$while_nr]['method']['has_close'] = true;
                                    $while_record['method']['has_close'] = true;
                                    if($is_block){
                                        $block[] = '}';
                                    } else {
                                        $data[] = '}';
                                    }
                                    $variable_assign_next_tag = true;
                                    $break_level--;
                                    $object->config('package.raxon/parse.build.state.break.level', $break_level);
                                    break; //only 1 at a time
                                }
                            }
                            if ($has_close === false) {
                                if (
                                    array_key_exists('is_multiline', $record) &&
                                    $record['is_multiline'] === true
                                ) {
                                    throw new TemplateException(
                                        str_replace('\'', '\\\'', $record['tag']) . PHP_EOL .
                                        'Unused while close tag "{{/while}}" on line: ' .
                                        $record['line']['start'] .
                                        ', column: ' .
                                        $record['column'][$record['line']['start']]['start'] .
                                        ' in source: ' .
                                        $source,
                                    );
                                } else {
                                    throw new TemplateException(
                                        str_replace('\'', '\\\'', $record['tag']) . PHP_EOL .
                                        'Unused while close tag "{{/while}}" on line: ' .
                                        $record['line'] .
                                        ', column: ' .
                                        $record['column']['start'] .
                                        ' in source: ' .
                                        $source,
                                    );
                                }
                            }
                            $while = array_reverse($while_reverse);
                        }
                        elseif (
                            in_array(
                                $record['marker']['name'],
                                [
                                    'for',
                                ],
                                true
                            )
                        ) {
                            $for_reverse = array_reverse($for);
                            $has_close = false;
                            foreach ($for_reverse as $for_nr => $for_record) {
                                if (
                                    array_key_exists('method', $for_record) &&
                                    array_key_exists('has_close', $for_record['method']) &&
                                    $for_record['method']['has_close'] === true
                                ) {
                                    //skip
                                } elseif (
                                    array_key_exists('method', $for_record)
                                ) {
                                    $has_close = true;
                                    $for_reverse[$for_nr]['method']['has_close'] = true;
                                    $for_record['method']['has_close'] = true;
                                    if($is_block){
                                        $block[] = '}';
                                    } else {
                                        $data[] = '}';
                                    }
                                    $variable_assign_next_tag = true;
                                    $break_level--;
                                    $object->config('package.raxon/parse.build.state.break.level', $break_level);
                                    break; //only 1 at a time
                                }
                            }
                            if ($has_close === false) {
                                if (
                                    array_key_exists('is_multiline', $record) &&
                                    $record['is_multiline'] === true
                                ) {
                                    throw new TemplateException(
                                        str_replace('\'', '\\\'', $record['tag']) . PHP_EOL .
                                        'Unused for close tag "{{/for}}" on line: ' .
                                        $record['line']['start'] .
                                        ', column: ' .
                                        $record['column'][$record['line']['start']]['start'] .
                                        ' in source: ' .
                                        $source,
                                    );
                                } else {
                                    throw new TemplateException(
                                        str_replace('\'', '\\\'', $record['tag']) . PHP_EOL .
                                        'Unused for close tag "{{/for}}" on line: ' .
                                        $record['line'] .
                                        ', column: ' .
                                        $record['column']['start'] .
                                        ' in source: ' .
                                        $source,
                                    );
                                }
                            }
                            $for = array_reverse($for_reverse);
                        }
                        elseif (
                            in_array(
                                $record['marker']['name'],
                                [
                                    'if',
                                ],
                                true
                            )
                        ) {
                            $if_reverse = array_reverse($if);
                            $has_close = false;
                            foreach ($if_reverse as $if_nr => $if_record) {
                                if (
                                    array_key_exists('method', $if_record) &&
                                    array_key_exists('has_close', $if_record['method']) &&
                                    $if_record['method']['has_close'] === true
                                ) {
                                    //skip
                                } elseif (
                                    array_key_exists('method', $if_record)
                                ) {
                                    $has_close = true;
                                    $if_reverse[$if_nr]['method']['has_close'] = true;
                                    $if_record['method']['has_close'] = true;
                                    if($is_block){
                                        $block[] = '}';
                                    } else {
                                        $data[] = '}';
                                    }
                                    $variable_assign_next_tag = true;
                                    break; //only 1 at a time
                                }
                            }
                            if ($has_close === false) {
                                if (
                                    array_key_exists('is_multiline', $record) &&
                                    $record['is_multiline'] === true
                                ) {
                                    throw new TemplateException(
                                        str_replace('\'', '\\\'', $record['tag']) . PHP_EOL .
                                        'Unused if close tag "{{/if}}" on line: ' .
                                        $record['line']['start'] .
                                        ', column: ' .
                                        $record['column'][$record['line']['start']]['start'] .
                                        ' in source: ' .
                                        $source,
                                    );

                                } else {
                                    throw new TemplateException(
                                        str_replace('\'', '\\\'', $record['tag']) . PHP_EOL .
                                        'Unused if close tag "{{/if}}" on line: ' .
                                        $record['line'] .
                                        ', column: ' .
                                        $record['column']['start'] .
                                        ' in source: ' .
                                        $source,
                                    );
                                }
                            }
                            $if = array_reverse($if_reverse);
                        }
                        elseif (
                            array_key_exists('marker', $record) &&
                            in_array(
                                $record['marker']['name'],
                                [
                                    'block',
                                ],
                                true
                            )
                        ) {
                            $data[] = 'ob_start();';
                            foreach($block as $block_nr => $block_record){
                                $data[] = $block_record;
                            }
                            $method = $object->config('package.raxon/parse.build.state.block.record');
                            $plugin = $object->config('package.raxon/parse.build.state.block.plugin');
                            $data[] = '$block = rtrim(ob_get_clean());';
                            $data[] = '$block = Core::object($block, Core::OBJECT_OBJECT);';
                            $data[] = '$source = $options->source ?? null;';
                            $data[] = '$class = $options->class ?? null;';
                            $data[] = '$options->source = \'internal_' . Core::uuid() . '\';';
                            $data[] = '$options->class = Parse::class_name($object, $options->source);';
                            $data[] = '$block = $parse->compile($block, $data);';
                            $data[] = '$options->source = $source;';
                            $data[] = '$options->class = $class;';
                            $argument = [];
                            if(
                                array_key_exists('method', $method) &&
                                array_key_exists('argument', $method['method'])
                            ){
                                foreach($method['method']['argument'] as $argument_nr => $argument_record){
                                    $value = Build::value($object, $flags, $options, $record, $argument_record. $is_set);
                                    $argument[] = $value;
                                }
                            }
                            $method_value = '$this->' . $plugin . '(' . PHP_EOL;
                            $method_value .= '$block,' . PHP_EOL;
                            $is_argument = false;
                            foreach($argument as $argument_nr => $argument_record){
                                if($argument_record !== ''){
                                    $method_value .= $argument_record . ',' . PHP_EOL;
                                    $is_argument = true;
                                }
                            }
                            if($is_argument){
                                $method_value = substr($method_value, 0, -2) . PHP_EOL;
                            }
                            $data[] = $method_value . ');';
                            $is_block = false;
                            //there is plugin name and record with the arguments
                            $object->config('delete', 'package.raxon/parse.build.state.block');
                            $variable_assign_next_tag = true;
                        }
                        elseif (
                            array_key_exists('marker', $record) &&
                            in_array(
                                $record['marker']['name'],
                                [
                                    'literal',
                                ],
                                true
                            )
                        ) {
                            $is_literal = false;
                            $variable_assign_next_tag = true;
                        }
                    }
                }
                elseif (
                    array_key_exists('marker', $record) &&
                    in_array(
                        $record['marker']['name'],
                        [
                            'else',
                        ],
                        true
                    )
                ) {
                    if($is_block){
                        $block[] = '} else {';
                    } else {
                        $data[] = '} else {';
                    }
                    $variable_assign_next_tag = true;
                }
                elseif (
                    array_key_exists('marker', $record) &&
                    in_array(
                        $record['marker']['name'],
                        [
                            'literal',
                        ],
                        true
                    )
                ) {
                    $is_literal = true;
                    $variable_assign_next_tag = true;
                }
                elseif(array_key_exists('marker', $record)){
                    $class_static = Build::class_static($object);
                    if(
                        array_key_exists('value', $record['marker']) &&
                        array_key_exists('array', $record['marker']['value']) &&
                        array_key_exists(0, $record['marker']['value']['array']) &&
                        array_key_exists('type', $record['marker']['value']['array'][0]) &&
                        $record['marker']['value']['array'][0]['type'] === 'symbol' &&
                        array_key_exists(1, $record['marker']['value']['array']) &&
                        array_key_exists('type', $record['marker']['value']['array'][1]) &&
                        $record['marker']['value']['array'][1]['type'] === 'variable'
                        //add method
                    ){
                        // !!!! $this.boolean
                        $value = Build::value($object, $flags, $options, $record, $record['marker']['value'], $is_set);
                        $uuid_variable = Core::uuid_variable();
                        if($is_block){
                            $block[] = $uuid_variable . ' =  ' . $value . ';';
                            $block[] = 'if(' . $uuid_variable . ' === true){';
                            $block[] = 'echo \'true\';';
                            $block[] = '} else {';
                            $block[] = 'echo \'false\';';
                            $block[] = '}';
                        } else {
                            $data[] = $uuid_variable . ' =  ' . $value . ';';
                            $data[] = 'if(' . $uuid_variable . ' === true){';
                            $data[] = 'echo \'true\';';
                            $data[] = '} else {';
                            $data[] = 'echo \'false\';';
                            $data[] = '}';
                        }
                    }
                    elseif(
                        in_array(
                            $record['marker']['name'],
                            $class_static,
                            true
                        ) &&
                        array_key_exists('value', $record['marker'])
                    ){
                        //this should be able to be disabled, (security)
                        $name = $record['marker']['value']['array'][2]['method']['name'];
                        $argument = $record['marker']['value']['array'][2]['method']['argument'];
                        foreach($argument as $argument_nr => $argument_record){
                            $value = Build::value($object, $flags, $options, $record, $argument_record, $is_set);
                            $argument[$argument_nr] = $value;
                        }
                        if($is_block){
                            if(array_key_exists(0, $argument)){
                                $block[] = $record['marker']['name'] . $name . '(' . implode(', ', $argument) . ');';
                            } else {
                                $block[] = $record['marker']['name'] . $name . '();';
                            }
                        } else {
                            if(array_key_exists(0, $argument)){
                                $data[] = $record['marker']['name'] . $name . '(' . implode(', ', $argument) . ');';
                            } else {
                                $data[] = $record['marker']['name'] . $name . '();';
                            }
                        }
                    }
                    else {
                        if(
                            array_key_exists('is_multiline', $record) &&
                            $record['is_multiline'] === true
                        ){
                            breakpoint($record);
                            throw new TemplateException(
                                $record['tag'] . PHP_EOL .
                                'Unknown marker "{{' . $record['marker']['name'] .'}}" on line: ' .
                                $record['line']['start']  .
                                ', column: ' .
                                $record['column'][$record['line']['start']]['start'] .
                                ' in source: '.
                                $source,
                            );

                        } else {
                            breakpoint($record);
                            throw new TemplateException(
                                $record['tag'] . PHP_EOL .
                                'Unknown marker "{{' . $record['marker']['name'] .'}}" on line: ' .
                                $record['line'] .
                                ', column: ' .
                                $record['column']['start'] .
                                ' in source: '.
                                $source,
                            );
                        }
                    }
                }
            }
        }
        foreach($foreach as $foreach_nr => $foreach_record){
            if(
                array_key_exists('method', $foreach_record) &&
                array_key_exists('has_close', $foreach_record['method']) &&
                $foreach_record['method']['has_close'] === true
            ){
                //skip
            } elseif(
                array_key_exists('method', $foreach_record)
            ) {
                if(
                    array_key_exists('is_multiline', $foreach_record) &&
                    $foreach_record['is_multiline'] === true
                ){
                    throw new TemplateException(
                        $foreach_record['tag'] . PHP_EOL .
                        'Unclosed foreach open tag "{{foreach()}}" on line: ' .
                        $foreach_record['line']['start']  .
                        ', column: ' .
                        $foreach_record['column'][$foreach_record['line']['start']]['start'] .
                        ' in source: '.
                        $source,
                    );
                } else {
                    throw new TemplateException(
                        $foreach_record['tag'] . PHP_EOL .
                        'Unclosed foreach open tag "{{foreach()}}" on line: ' .
                        $foreach_record['line'] .
                        ', column: ' .
                        $foreach_record['column']['start'] .
                        ' in source: '.
                        $source,
                    );
                }
            }
        }
        foreach($while as $while_nr => $while_record){
            if(
                array_key_exists('method', $while_record) &&
                array_key_exists('has_close', $while_record['method']) &&
                $while_record['method']['has_close'] === true
            ){
                //skip
            } elseif(
                array_key_exists('method', $while_record)
            ) {
                if(
                    array_key_exists('is_multiline', $while_record) &&
                    $while_record['is_multiline'] === true
                ){
                    throw new TemplateException(
                        $while_record['tag'] . PHP_EOL .
                        'Unclosed while open tag "{{while()}}" on line: ' .
                        $while_record['line']['start']  .
                        ', column: ' .
                        $while_record['column'][$while_record['line']['start']]['start'] .
                        ' in source: '.
                        $source,
                    );

                } else {
                    throw new TemplateException(
                        $while_record['tag'] . PHP_EOL .
                        'Unclosed while open tag "{{while()}}" on line: ' .
                        $while_record['line'] .
                        ', column: ' .
                        $while_record['column']['start'] .
                        ' in source: '.
                        $source,
                    );
                }
            }
        }
        foreach($for as $for_nr => $for_record){
            if(
                array_key_exists('method', $for_record) &&
                array_key_exists('has_close', $for_record['method']) &&
                $for_record['method']['has_close'] === true
            ){
                //skip
            } elseif(
                array_key_exists('method', $for_record)
            ) {
                if(
                    array_key_exists('is_multiline', $for_record) &&
                    $for_record['is_multiline'] === true
                ){
                    throw new TemplateException(
                        $for_record['tag'] . PHP_EOL .
                        'Unclosed while open tag "{{while()}}" on line: ' .
                        $for_record['line']['start']  .
                        ', column: ' .
                        $for_record['column'][$for_record['line']['start']]['start'] .
                        ' in source: '.
                        $source,
                    );

                } else {
                    throw new TemplateException(
                        $for_record['tag'] . PHP_EOL .
                        'Unclosed while open tag "{{while()}}" on line: ' .
                        $for_record['line'] .
                        ', column: ' .
                        $for_record['column']['start'] .
                        ' in source: '.
                        $source,
                    );
                }
            }
        }
        foreach($if as $if_nr => &$if_record){
            if(
                array_key_exists('method', $if_record) &&
                array_key_exists('has_close', $if_record['method']) &&
                $if_record['method']['has_close'] === true
            ){
                //skip
            } elseif(
                array_key_exists('method', $if_record)
            ) {
                if(
                    array_key_exists('is_multiline', $if_record) &&
                    $if_record['is_multiline'] === true
                ){
                    throw new TemplateException(
                        $if_record['tag'] . PHP_EOL .
                        'Unclosed while open tag "{{while()}}" on line: ' .
                        $if_record['line']['start']  .
                        ', column: ' .
                        $if_record['column'][$if_record['line']['start']]['start'] .
                        ' in source: '.
                        $source,
                    );

                } else {
                    throw new TemplateException(
                        $if_record['tag'] . PHP_EOL .
                        'Unclosed while open tag "{{while()}}" on line: ' .
                        $if_record['line'] .
                        ', column: ' .
                        $if_record['column']['start'] .
                        ' in source: '.
                        $source,
                    );
                }
            }
        }
        return $data;
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
        $document[] = str_repeat(' ', $indent * 4) . '$this->flags($flags);';
        $document[] = str_repeat(' ', $indent * 4) . '$this->options($options);';
        $object->config(
            'package.raxon/parse.build.state.indent',
            $object->config('package.raxon/parse.build.state.indent') - 1
        );
        $indent = $object->config('package.raxon/parse.build.state.indent');
        $document[] = str_repeat(' ', $indent * 4) . '}';
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
        $build = new Build($object, $flags, $options);
        $indent = $object->config('package.raxon/parse.build.state.indent');
        $document = Build::document_run_throw($object, $flags, $options, $document);
        $document[] = str_repeat(' ', $indent * 4) . 'public function run(): mixed';
        $document[] = str_repeat(' ', $indent * 4) . '{';
        $indent++;
        $document[] = str_repeat(' ', $indent * 4) . 'ob_start();';
        $document[] = str_repeat(' ', $indent * 4) . '$object = $this->object();';
        $document[] = str_repeat(' ', $indent * 4) . '$parse = $this->parse();';
        $document[] = str_repeat(' ', $indent * 4) . '$data = $this->data();';
        $document[] = str_repeat(' ', $indent * 4) . '$flags = $this->flags();';
        $document[] = str_repeat(' ', $indent * 4) . '$options = $this->options();';
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
        $document = Build::format($build, $document, $data, $indent);
        $document[] = str_repeat(' ', $indent * 4) . 'return ob_get_clean();';
        $indent--;
        $document[] = str_repeat(' ', $indent * 4) . '}';
        return $document;
    }

    public static function format(Build $build, $document=[], $data=[], $indent=2): array
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
                        $result[] = 'echo \'' . str_replace('\'', '\\\'', $line) . '\';' . PHP_EOL;
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
                        $result[] = 'echo \'' . $line . '\';' . PHP_EOL;
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
                        $result[] = 'echo \'' . $line . '\';' . PHP_EOL;
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
                    $result[] = 'echo \'' . $line . '\';' . PHP_EOL;
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
    
    public static function variable_assign_next(App $object, $flags, $options,$record = [], $next=[]){
        if(!array_key_exists('variable', $record)){
            return $next;
        }
        elseif(
            !array_key_exists('is_assign', $record['variable']) ||
            $record['variable']['is_assign'] !== true
        ) {
            return $next;
        }
        if(
            array_key_exists('text', $next) &&
            array_key_exists('is_multiline', $next) &&
            $next['is_multiline'] === true
        ){
            $data = mb_str_split($next['text']);
            $is_single_quote = false;
            $is_double_quote = false;
            $test = '';
            foreach($data as $nr => $char){
                if(
                    $char === '\'' &&
                    $is_double_quote === false &&
                    $is_single_quote === false
                ){
                    $is_single_quote = true;
                }
                elseif(
                    $char === '\'' &&
                    $is_double_quote === false &&
                    $is_single_quote === true
                ){
                    $is_single_quote = false;
                }
                elseif(
                    $char === '"' &&
                    $is_double_quote === false &&
                    $is_single_quote === false
                ){
                    $is_double_quote = true;
                }
                elseif(
                    $char === '"' &&
                    $is_double_quote === true &&
                    $is_single_quote === false
                ){
                    $is_double_quote = false;
                }
                if(
                    $char === "\n" &&
                    $is_single_quote === false &&
                    $is_double_quote === false
                ){
                    $test = trim($test);
                    if($test === ''){
                        $next['text'] = mb_substr($next['text'], $nr + 1);
                    }
                    break;
                }
                $test .= $char;
            }
        }
        return $next;
    }

    /**
     * @throws Exception
     * @throws LocateException
     */
    public static function plugin(App $object, $flags, $options, $record, $name): string
    {
        $source = $options->source ?? '';
        $name_lowercase = mb_strtolower($name);
        if(
            in_array(
                $name_lowercase,
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
            $plugin = 'plugin_' . $name_lowercase;
        } else {
            $plugin = $name_lowercase;
        }
        $plugin = str_replace('.', '_', $plugin);
        $plugin = str_replace('-', '_', $plugin);
        $backslash_double = Core::uuid();
        $plugin = str_replace('\\\\', $backslash_double , $plugin);
        $plugin = str_replace('\\', '\\\\', $plugin);
        $plugin = str_replace($backslash_double, '\\\\', $plugin);
        $plugin = str_replace('\\\\', '_', $plugin);
        $is_smiley = false;
        $split = mb_str_split($name);
        $plugin_code_point = 'CodePoint_';
        breakpoint($split);
        foreach($split as $nr => $char){
            $ord = mb_ord($char);
            breakpoint($ord);
            if($ord >= 256){
                $is_code_point = true;
                $plugin_code_point .= $ord . '_';
            }
        }
        if($is_code_point){
            $plugin = substr($plugin_code_point, 0, -1);
            breakpoint($plugin);
        }
        $use_plugin = explode('_', $plugin);
        foreach($use_plugin as $nr => $use){
            $use_plugin[$nr] = ucfirst($use);
        }
        $use_plugin = 'Plugin\\' . implode('_', $use_plugin);

        $use = $object->config('package.raxon/parse.build.use.trait');
        $use_trait_function = $object->config('package.raxon/parse.build.use.trait_function');
        if(!$use){
            $use = [];
            $use_trait_function = [];
        }
        if(
            !in_array(
                $use_plugin,
                [
                    'Plugin\\Value_Concatenate',
                    'Plugin\\Value_Plus_Plus',
                    'Plugin\\Value_Minus_Minus',
                    'Plugin\\Value_Multiply_Multiply',
                    'Plugin\\Value_Plus',
                    'Plugin\\Value_Minus',
                    'Plugin\\Value_Multiply',
                    'Plugin\\Value_Modulo',
                    'Plugin\\Value_Divide',
                    'Plugin\\Value_Smaller',
                    'Plugin\\Value_Smaller_Equal',
                    'Plugin\\Value_Smaller_Smaller',
                    'Plugin\\Value_Greater',
                    'Plugin\\Value_Greater_Equal',
                    'Plugin\\Value_Greater_Greater',
                    'Plugin\\Value_Equal',
                    'Plugin\\Value_Identical',
                    'Plugin\\Value_Not_Equal',
                    'Plugin\\Value_Not_Identical',
                    'Plugin\\Value_And',
                    'Plugin\\Value_Or',
                    'Plugin\\Value_Xor',
                    'Plugin\\Value_Null_Coalescing',
                    'Plugin\\Value_Set',
                    'Plugin\\Framework',
                ],
                true
            )
        ){
            if(!in_array($use_plugin, $use, true)){
                $autoload = $object->data(App::AUTOLOAD_RAXON);
                $location = $autoload->locate($use_plugin, false,  Autoload::MODE_LOCATION);
                $exist = false;
                $locate_exception = [];
                foreach($location  as $nr => $fileList){
                    foreach($fileList as $file){
                        breakpoint($file);
                        $explode = explode('Smiley/Smiley.', $file, 2);
                        if(array_key_exists(1, $explode)){
                            $file = implode('Smiley/.Smiley.', $explode);
                        }
                        $locate_exception[] = $file;
                        $exist = File::exist($file);
                        if($exist){
                            break;
                        }
                    }
                }
                if($exist === false){
                    if(
                        array_key_exists('is_multiline', $record) &&
                        $record['is_multiline'] === true
                    ){
                        breakpoint($record);
                        throw new LocateException(
                            'Plugin not found (' .
                            str_replace('_', '.', $name) .
                            ') exception: "' .
                            str_replace('\'', '\\\'', $record['tag']) .
                            '" on line: ' .
                            $record['line']['start']  .
                            ', column: ' .
                            $record['column'][$record['line']['start']]['start'] .
                            ' in source: '.
                            $source,
                            $locate_exception
                        );
                    } else {
                        breakpoint($record);
                        throw new LocateException(
                            'Plugin not found (' .
                            str_replace('_', '.', $name) .
                            ') exception: "' .
                            str_replace('\'', '\\\'', $record['tag']) .
                            '" on line: ' .
                            $record['line']  .
                            ', column: ' .
                            $record['column']['start'] .
                            ' in source: '.
                            $source,
                            $locate_exception
                        );
                    }
                }
                $use[] = $use_plugin;
                $use_trait_function[count($use) - 1] = $plugin;
            }
        }
        $object->config('package.raxon/parse.build.use.trait', $use);
        $object->config('package.raxon/parse.build.use.trait_function', $use_trait_function);
        return mb_strtolower($plugin);
    }

    /**
     * @throws Exception
     * @throws LocateException
     */
    public static function variable_define(App $object, $flags, $options, $record = []): bool | array
    {
        if (!array_key_exists('variable', $record)) {
            return false;
        }
        elseif (
            !array_key_exists('is_define', $record['variable']) ||
            $record['variable']['is_define'] !== true
        ) {
            return false;
        }
        if(!array_key_exists('name', $record['variable'])){
            trace();
            ddd($record);
        }
        $source = $options->source ?? '';
        $variable_name = $record['variable']['name'];
        $variable_uuid = Core::uuid_variable();
        $method_value = '';
        if(
            array_key_exists('method', $record['variable']) &&
            array_key_exists('operator', $record['variable']) &&
            array_key_exists('name', $record['variable']['method'])
        ){
            $method_value .= $record['variable']['operator'] . $record['variable']['method']['name'] . '(' . PHP_EOL;
            $is_argument = false;
            if(array_key_exists('argument', $record['variable']['method'])){
                foreach($record['variable']['method']['argument'] as $argument_nr => $argument){
                    $argument = Build::value($object, $flags, $options, $record, $argument, $is_set);
                    if($argument !== ''){
                        $method_value .= $argument . ',' . PHP_EOL;
                        $is_argument = true;
                    }
                }
                if($is_argument === true){
                    $method_value = mb_substr($method_value, 0, -2) . PHP_EOL . ')' . PHP_EOL;
                } else {
                    $method_value .= ')' . PHP_EOL;
                }
            }
        }
        if(array_key_exists('modifier', $record['variable'])){
            $previous_modifier = '$data->get(\'' . $variable_name . '\')' . $method_value;
            $modifier_value = $previous_modifier;
            foreach($record['variable']['modifier'] as $nr => $modifier){
                $plugin = Build::plugin($object, $flags, $options, $record, str_replace('.', '_', $modifier['name']));
                $modifier_value = '$this->' . $plugin . '(' . PHP_EOL;
                $modifier_value .= $previous_modifier . ',' . PHP_EOL;
                $is_argument = false;
                if(array_key_exists('argument', $modifier)){
                    foreach($modifier['argument'] as $argument_nr => $argument){
                        $argument = Build::value($object, $flags, $options, $record, $argument, $is_set);
                        if($argument !== ''){
                            $modifier_value .= $argument . ',' . PHP_EOL;
                            $is_argument = true;
                        }
                    }
                    if($is_argument === true){
                        $modifier_value = mb_substr($modifier_value, 0, -2) . PHP_EOL;
                    } else {
                        $modifier_value = mb_substr($modifier_value, 0, -2);
                    }
                }
                $modifier_value .= ')';
                $previous_modifier = $modifier_value;
            }
            $value = $modifier_value;
            $is_not = '';
            if(array_key_exists('is_not', $record['variable'])){
                if($record['variable']['is_not'] === true){
                    $is_not = ' !! ';
                }
                elseif($record['variable']['is_not'] === false){
                    $is_not = ' !';
                }
            }
            if(
                array_key_exists('cast', $record['variable']) &&
                $record['variable']['cast'] !== false
            ){
                if($record['variable']['cast'] === 'clone'){
                    $value = 'clone ' . $value;
                } else {
                    $value = '(' . $record['variable']['cast'] . ') ' . $value;
                }
            }
            $data = [
                'try {',
                $variable_uuid . ' = ' . $is_not . $value . ';',
            ];
            if(
                array_key_exists('is_multiline', $record) &&
                $record['is_multiline'] === true
            ){
                $data[] = 'if(' . $variable_uuid .' === null){';
                $data[] = 'ob_end_clean();';
//                $data[] = 'ddd($data);';
                $data[] = 'throw new TemplateException(\'Null-pointer exception: "$' . $variable_name . str_replace('\'', '\\\'', $method_value) . '" on line: ' . $record['line']['start']  . ', column: ' . $record['column'][$record['line']['start']]['start'] . ' in source: '. $source . '. You can use modifier "default" to surpress it \');';
                $data[] = '}';
            } else {
                $data[] = 'if(' . $variable_uuid .' === null){';
                $data[] = 'ob_end_clean();';
//                $data[] = 'ddd($data);';
                $data[] = 'throw new TemplateException(\'Null-pointer exception: "$' . $variable_name . str_replace('\'', '\\\'', $method_value) . '" on line: ' . $record['line']  . ', column: ' . $record['column']['start'] . ' in source: '. $source . '. You can use modifier "default" to surpress it \');';
                $data[] = '}';
            }
//            $data[] = 'd(' . $variable_uuid . ');';
            $data[] = 'if(!is_scalar('. $variable_uuid. ')){';
            $data[] = '//array or object';
            $data[] = 'ob_get_clean();';
            $data[] = 'return ' . $variable_uuid .';';
            $data[] = '}';
            $data[] = 'elseif(is_bool('. $variable_uuid. ')){';
            $data[] = 'return ' . $variable_uuid .';';
            $data[] = '} else {';
            $data[] = 'echo '. $variable_uuid .';';
            $data[] = '}';
            $data[] = '} catch (Exception $exception) {'; //catch
            if(
                array_key_exists('is_multiline', $record) &&
                $record['is_multiline'] === true
            ){
                $data[] = 'if(' . $variable_uuid .' === null){';
                $data[] = 'ob_end_clean();';
//                $data[] = 'ddd($data);';
                $data[] = 'throw new TemplateException(\'Null-pointer exception: "$' . $variable_name . str_replace('\'', '\\\'', $method_value) . '" on line: ' . $record['line']['start']  . ', column: ' . $record['column'][$record['line']['start']]['start'] . ' in source: '. $source . '. You can use modifier "default" to surpress it \');';
                $data[] = '}';
            } else {
                $data[] = 'if(' . $variable_uuid .' === null){';
                $data[] = 'ob_end_clean();';
//                $data[] = 'ddd($data);';
                $data[] = 'throw new TemplateException(\'Null-pointer exception: "$' . $variable_name . str_replace('\'', '\\\'', $method_value) . '" on line: ' . $record['line']  . ', column: ' . $record['column']['start'] . ' in source: '. $source . '. You can use modifier "default" to surpress it \');';
                $data[] = '}';
            }
            $data[] = '}';
            return $data;
        } else {
            $is_not = '';
            if(
                array_key_exists('is_not', $record['variable'])
            ){
                if($record['variable']['is_not'] === true){
                    $is_not = '!! ';
                }
                elseif($record['variable']['is_not'] === false){
                    $is_not = '! ';
                }
            }
            $cast = '';
            if(
                array_key_exists('cast', $record['variable']) &&
                $record['variable']['cast'] !== false
            ){
                if($record['variable']['cast'] === 'clone'){
                    $cast = 'clone ';
                } else {
                    $cast = '(' . $record['variable']['cast'] . ') ';
                }
            }
            $data = [
                $variable_uuid . ' = ' . $is_not . $cast . '$data->get(\'' . $variable_name . '\');' ,
            ];
            if(
                array_key_exists('is_multiline', $record) &&
                $record['is_multiline'] === true
            ){
                $data[] = 'if(' . $variable_uuid .' === null){';
                $data[] = 'ob_end_clean();';
//                $data[] = 'ddd($data);';
                $data[] = 'throw new TemplateException(\'Null-pointer exception: "$' . $variable_name . str_replace('\'', '\\\'', $method_value) .'" on line: ' . $record['line']['start']  . ', column: ' . $record['column'][$record['line']['start']]['start'] . ' in source: '. $source . '. You can use modifier "default" to surpress it \');';
                $data[] = '}';
            } else {
                $data[] = 'if(' . $variable_uuid .' === null){';
                $data[] = 'ob_end_clean();';
//                $data[] = 'ddd($data);';
                $data[] = 'throw new TemplateException(\'Null-pointer exception: "$' . $variable_name . str_replace('\'', '\\\'', $method_value) .'" on line: ' . $record['line']  . ', column: ' . $record['column']['start'] . ' in source: ' . $source . '. You can use modifier "default" to surpress it \');';
                $data[] = '}';
            }
            $data[] = 'if(!is_scalar('. $variable_uuid. ')){';
            $data[] = '//array or object';
            $data[] = 'ob_get_clean();';
            $data[] = 'return ' . $variable_uuid .';';
            $data[] = '}';
            $data[] = 'elseif(is_bool('. $variable_uuid. ')){';
            $data[] = 'return ' . $variable_uuid .';';
            $data[] = '} else {';
            $data[] = 'echo '. $variable_uuid .';';
            $data[] = '}';
            return $data;;
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
                        if($instance->class === 'Raxon\Attribute\Argument'){
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
                $class_static = Build::class_static($object);
                if(
                    in_array(
                        $name,
                        $class_static,
                        true
                    )
                ) {
                    $name .= $argument['array'][2]['method']['name'];
                    $argument = $argument['array'][2]['method']['argument'];
                    foreach ($argument as $argument_nr => $argument_record) {
                        $value = Build::value($object, $flags, $options, $record, $argument_record, $is_set, $before,$after);
                        $uuid_variable = Core::uuid_variable();
                        $before[] = $uuid_variable . ' = ' . $value . ';';
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
                        breakpoint('test reference, need class in reflection');
                        $after[$argument_nr] = null;
                    }
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
                    $argument = '\'' . str_replace('\'', '\\\'', trim($argument['string'])) . '\'';
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
                    $argument = '\'' . str_replace('\'', '\\\'', trim($argument['string'])) . '\'';
                }
                elseif (
                    property_exists($argument_attribute, 'apply') &&
                    $argument_attribute->apply === 'literal' &&
                    property_exists($argument_attribute, 'index') &&
                    is_int($argument_attribute->index) &&
                    $argument_attribute->index === $nr
                ){
                    //we have a single index
                    $argument = '\'' . str_replace('\'', '\\\'', trim($argument['string'])) . '\'';
                } else {
                    $argument = Build::value($object, $flags, $options, $record, $argument, $is_set, $before, $after);
                    $uuid_variable = Core::uuid_variable();
                    $before[] = $uuid_variable . ' = ' . $argument . ';';
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
     * @throws LocateException
     * @throws TemplateException
     */
    public static function method(App $object, $flags, $options, $record=[]): bool | string
    {
        if(!array_key_exists('method', $record)){
            return false;
        }
        $source = $options->source ?? '';
        $method_name = mb_strtolower($record['method']['name']);
        $before = [];
        $after = [];
        switch($method_name){
            case 'for.each':
            case 'for_each':
            case 'foreach':
                $foreach_from = $record['method']['argument'][0]['array'][0] ?? null;
                $foreach_key = $record['method']['argument'][0]['array'][2] ?? null;
                $foreach_value = $record['method']['argument'][0]['array'][4] ?? null;
                if($foreach_value === null){
                    $foreach_value = $foreach_key;
                    $foreach_key = null;
                    $key = null;
                } else {
                    $key = Core::uuid_variable();
                }
                if(
                    !array_key_exists('tag', $foreach_from) &&
                    array_key_exists('type', $foreach_from) &&
                    $foreach_from['type'] === 'array'
                ){
                    $value = [
                        'string' => $foreach_from['string'],
                        'array' => [
                            0 => $foreach_from
                        ]
                    ];
                }
                elseif(array_key_exists('tag', $foreach_from)) {
                    $value = [
                        'string' => $foreach_from['tag'],
                        'array' => [
                            0 => $foreach_from
                        ]
                    ];
                } elseif(
                    array_key_exists('is_multiline', $record) &&
                    $record['is_multiline'] === true
                ){
                    //invalid from
                    throw new TemplateException(str_replace('\'', '\\\'', $record['tag']) . PHP_EOL . 'On line: ' . $record['line']['start']  . ', column: ' . $record['column'][$record['line']['start']]['start'] . ' in source: '. $source . '.');
                } else {
                    //invalid from
                    throw new TemplateException(str_replace('\'', '\\\'', $record['tag']) . PHP_EOL . 'On line: ' . $record['line']  . ', column: ' . $record['column']['start'] . ' in source: ' . $source . '.');
                }
                if($key){
                    if(
                        array_key_exists('type', $foreach_key) &&
                        $foreach_key['type'] === 'variable'
                    ){
                        //nothing
                    } elseif(
                        array_key_exists('is_multiline', $record) &&
                        $record['is_multiline'] === true
                    ){
                        //invalid key
                        throw new TemplateException(str_replace('\'', '\\\'', $record['tag']) . PHP_EOL . 'On line: ' . $record['line']['start']  . ', column: ' . $record['column'][$record['line']['start']]['start'] . ' in source: '. $source . '.');
                    } else {
                        //invalid key
                        throw new TemplateException(str_replace('\'', '\\\'', $record['tag']) . PHP_EOL . 'On line: ' . $record['line']  . ', column: ' . $record['column']['start'] . ' in source: ' . $source . '.');
                    }
                }
                if(
                    array_key_exists('type', $foreach_value) &&
                    $foreach_value['type'] === 'variable'
                ){
                    //nothing
                } elseif(
                    array_key_exists('is_multiline', $record) &&
                    $record['is_multiline'] === true
                ){
                    //invalid value
                    throw new TemplateException(str_replace('\'', '\\\'', $record['tag']) . PHP_EOL . 'On line: ' . $record['line']['start']  . ', column: ' . $record['column'][$record['line']['start']]['start'] . ' in source: '. $source . '.');
                } else {
                    //invalid value
                    throw new TemplateException(str_replace('\'', '\\\'', $record['tag']) . PHP_EOL . 'On line: ' . $record['line']  . ', column: ' . $record['column']['start'] . ' in source: ' . $source . '.');
                }
                $foreach_from = Build::value($object, $flags, $options, $record, $value, $is_set);
                $from = Core::uuid_variable();
                $value = Core::uuid_variable();
                $method_value = [];
                $method_value[] = $from . ' = ' . $foreach_from . ';';
                $method_value[] = '$type = str_replace(\'double\', \'float\', gettype(' . $from . '));';
                $method_value[] = 'if(!in_array($type, [\'array\', \'object\'], true)){';
                if(
                    array_key_exists('is_multiline', $record) &&
                    $record['is_multiline'] === true
                ){
                    $method_value[] = 'throw new TemplateException(\'' . str_replace('\'', '\\\'', $record['tag']) . PHP_EOL . 'Invalid argument type: \' . $type . \' for foreach on line: ' . $record['line']['start']  . ', column: ' . $record['column'][$record['line']['start']]['start'] . ' in source: ' . $source . '\');';
                } else {
                    $method_value[] = 'throw new TemplateException(\'' . str_replace('\'', '\\\'', $record['tag']) . PHP_EOL . 'Invalid argument type: \' . $type . \' for foreach on line: ' . $record['line']  . ', column: ' . $record['column']['start'] . ' in source: ' . $source . '\');';
                }
                $method_value[] = '}';
                if($key){
                    $method_value[] = 'foreach(' . $from . ' as ' . $key . ' => ' . $value . '){';
                    $foreach_set =[];
                    $foreach_set[] = '$data->set(\'' . $foreach_key['name'] . '\', ' . $key . ');';
                    $foreach_set[] = '$data->set(\'' . $foreach_value['name'] . '\', ' . $value . ');';
                    $foreach_value = implode(PHP_EOL, $foreach_set);
                } else {
                    $method_value[] = 'foreach(' . $from . ' as ' . $value . '){';
                    $foreach_value = '$data->set(\'' . $foreach_value['name'] . '\', ' . $value . ');';
                }

                $method_value[] = $foreach_value . PHP_EOL;
                $method_value = implode(PHP_EOL, $method_value);
            break;
            case 'while':
                $method_value[] = 'while(';
                $is_argument = false;
                foreach($record['method']['argument'] as $nr => $argument){
                    $value = Build::value($object, $flags, $options, $record, $argument, $is_set, $before, $after);
                    if(
                        !in_array(
                            $value,
                            [
                                null,
                                ''
                            ],
                        true
                        )
                    ){
                        $is_argument = true;
                    }
                    $method_value[] = $value;
                }
                if($is_argument === false){
                    if(
                        array_key_exists('is_multiline', $record) &&
                        $record['is_multiline'] === true
                    ){
                        throw new TemplateException(
                            $record['tag'] .
                            PHP_EOL .
                            'Invalid argument for {{while()}}' .
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
                            'Invalid argument for {{while()}}' .
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
                $method_value[] = '){';
                $method_value = implode(PHP_EOL, $method_value);
                /*
                $method_value = implode(PHP_EOL, $before) .
                    PHP_EOL .
                    implode(PHP_EOL, $method_value) .
                    implode(PHP_EOL, $after)
                ;
                */
            break;
            case 'for':
                $method_value[] = 'for(';
                $is_argument = false;
                $argument_count = count($record['method']['argument']);
                if($argument_count === 3){
                    foreach($record['method']['argument'] as $nr => $argument){
                        $value = Build::value($object, $flags, $options, $record, $argument, $is_set);
                        if(mb_strtolower($value) === 'null'){
                            $value = '';
                        }
                        $method_value[] = $value . ';';
                    }
                    $method_value[3] = substr($method_value[3], 0, -1);
                    $is_argument = true;
                }
                if($is_argument === false){
                    if(
                        array_key_exists('is_multiline', $record) &&
                        $record['is_multiline'] === true
                    ){
                        throw new TemplateException(
                            $record['tag'] .
                            PHP_EOL .
                            'Invalid argument for {{for()}}' .
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
                            'Invalid argument for {{for()}}' .
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
                $method_value[] = '){';
                $method_value = implode(PHP_EOL, $method_value);
            break;
            case 'if':
            case 'elseif':
                if($method_name === 'elseif'){
                    $method_value[] = '} ' . PHP_EOL . 'elseif(';
                } else {
                    $method_value[] = 'if(';
                }
                $is_argument = false;
                foreach($record['method']['argument'] as $nr => $argument){
                    $value = Build::value($object, $flags, $options, $record, $argument, $is_set);
                    if(
                        !in_array(
                            $value,
                            [
                                null,
                                ''
                            ],
                            true
                        )
                    ){
                        $is_argument = true;
                    }
                    $method_value[] = $value;
                }
                if($is_argument === false){
                    if(
                        array_key_exists('is_multiline', $record) &&
                        $record['is_multiline'] === true
                    ){
                        throw new TemplateException(
                            $record['tag'] .
                            PHP_EOL .
                            'Invalid argument for {{if()}}' .
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
                            'Invalid argument for {{if()}}' .
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
                $method_value[] = '){';
                $method_value = implode(PHP_EOL, $method_value);
                break;
            case 'break' :
            case 'continue' :
                $is_argument = false;
                $value = false;
                if(
                    array_key_exists('argument', $record['method']) &&
                    is_array($record['method']['argument']) &&
                    array_key_exists(0, $record['method']['argument'])
                ) {
                    if(
                        is_array($record['method']['argument'][0]) &&
                        array_key_exists('array', $record['method']['argument'][0]) &&
                        is_array($record['method']['argument'][0]['array']) &&
                        array_key_exists(0, $record['method']['argument'][0]['array']) &&
                        is_array($record['method']['argument'][0]['array'][0]) &&
                        array_key_exists('type', $record['method']['argument'][0]['array'][0]) &&
                        $record['method']['argument'][0]['array'][0]['type'] === 'variable'
                    ){
                        if(
                            array_key_exists('is_multiline', $record) &&
                            $record['is_multiline'] === true
                        ){
                            throw new TemplateException(
                                $record['tag'] .
                                PHP_EOL .
                                $method_name . ' operator with non-integer operand is no longer supported...' .
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
                                $method_name . ' operator with non-integer operand is no longer supported...' .
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
                    } else {
                        $value = Build::value($object, $flags, $options, $record, $record['method']['argument'][0], $is_set);
                        $is_argument = true;
                    }
                }
                if(
                    $is_argument === false ||
                    mb_strtolower($value) === 'null'
                ){
                    $method_value = $method_name . ';';
                }
                elseif(
                    $method_name === 'break' &&
                    is_numeric($value) &&
                    is_int(($value + 0))
                ) {
                    $level = $value + 0;
                    $break_level = $object->config('package.raxon/parse.build.state.break.level');
                    if($level > $break_level){
                        if(
                            array_key_exists('is_multiline', $record) &&
                            $record['is_multiline'] === true
                        ){
                            throw new TemplateException(
                                $record['tag'] .
                                PHP_EOL .
                                'Cannot \'break\' ' . $value . ' levels for {{break()}}, only ' . $break_level . ' is allowed here...' .
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
                                'Cannot \'break\' ' . $value . ' levels for {{break()}}, only ' . $break_level . ' is allowed here...' .
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
                    $method_value = 'break ' . $value . ';';
                } elseif($method_name === 'break') {
                    if(
                        array_key_exists('is_multiline', $record) &&
                        $record['is_multiline'] === true
                    ){
                        throw new TemplateException(
                            $record['tag'] .
                            PHP_EOL .
                            'Invalid argument for {{break()}}, only numeric integer is allowed' .
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
                            'Invalid argument for {{break()}}, only numeric integer is allowed' .
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
                } else {
                    if(
                        array_key_exists('is_multiline', $record) &&
                        $record['is_multiline'] === true
                    ){
                        throw new TemplateException(
                            $record['tag'] .
                            PHP_EOL .
                            'Invalid argument for {{continue()}}, empty argument required' .
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
                            'Invalid argument for {{continue()}}, empty argument required' .
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
            break;
            case 'block.data':
            case 'block.html':
            case 'block.xml':
            case 'block.script':
            case 'block.link':
            case 'block.function':
            case 'block.code':
                $plugin = Build::plugin($object, $flags, $options, $record, str_replace('.', '_', $record['method']['name']));
//                $method_value = '$this->' . $plugin . '(';
                $object->config('package.raxon/parse.build.state.block.record', $record);
                $object->config('package.raxon/parse.build.state.block.plugin', $plugin);
                //we do the rest in the marker /block
                return $method_name;
            default:
                if(
                    array_key_exists('is_class_method', $record['method']) &&
                    $record['method']['is_class_method'] === true
                ){
                    $explode = explode(':', $record['method']['class']);
                    if(array_key_exists(1, $explode)){
                        $class = '\\' . implode('\\', $explode);
                    } else {
                        $class_static = Build::class_static($object);
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
                    $method_value .= Build::argument($object, $flags, $options, $record, $before, $after);
                    $method_value .= ');';
                } else {
                    $plugin = Build::plugin($object, $flags, $options, $record, str_replace('.', '_', $record['method']['name']));
                    $method_value = '$this->' . $plugin . '(';
                    $method_value .= Build::argument($object, $flags, $options, $record, $before, $after);
                    $method_value .= ');';
                }
            break;
        }
        switch($method_name){
            case 'for.each':
            case 'for_each':
            case 'foreach':
            case 'for':
            case 'while':
            case 'if':
            case 'elseif':
                try {
                    if($method_name === 'elseif'){
                        $method_validate = 'if(true){' . PHP_EOL . $method_value;
                    } else {
                        $method_validate = $method_value;
                    }
                    Validator::validate($object, $flags, $options, $method_validate . '}');
                }
                catch(Exception $exception){
                    if(
                        array_key_exists('is_multiline', $record) &&
                        $record['is_multiline'] === true
                    ){
                        throw new TemplateException(
                            $record['tag'] .
                            PHP_EOL .
                            'Validation error...' .
                            PHP_EOL .
                            'On line: ' .
                            $record['line']['start']  .
                            ', column: ' .
                            $record['column'][$record['line']['start']]['start'] .
                            ' in source: '.
                            $source .
                            '.',
                            0,
                            $exception
                        );
                    } else {
                        throw new TemplateException(
                            $record['tag'] .
                            PHP_EOL .
                            'Validation error...' .
                            PHP_EOL .
                            'On line: ' .
                            $record['line']  .
                            ', column: ' .
                            $record['column']['start'] .
                            ' in source: ' .
                            $source .
                            '.',
                            0,
                            $exception
                        );
                    }
                }
                //will remove whitespace at the beginning of the line type text with block functions
                $ltrim = $object->config('package.raxon/parse.build.state.ltrim');
                if(!$ltrim){
                    $ltrim = 1;
                } else {
                    $ltrim++;
                }
                $object->config('package.raxon/parse.build.state.ltrim', $ltrim);
            break;
            case 'break' :
            case 'continue':
            case 'block.data':
            case 'block.html':
            case 'block.xml':
            case 'block.code':
            case 'block.script':
            case 'block.link':
            case 'block.function':
                //nothing, checks have been done already
            break;
            default:
                try {
                    Validator::validate($object, $flags, $options, $method_value);
                }
                catch(Exception $exception){
                    if(
                        array_key_exists('is_multiline', $record) &&
                        $record['is_multiline'] === true
                    ){
                        throw new TemplateException($record['tag'] . PHP_EOL . 'On line: ' . $record['line']['start']  . ', column: ' . $record['column'][$record['line']['start']]['start'] . ' in source: '. $source . '.', 0, $exception);
                    } else {
                        throw new TemplateException($record['tag'] . PHP_EOL . 'On line: ' . $record['line']  . ', column: ' . $record['column']['start'] . ' in source: ' . $source . '.', 0, $exception);
                    }
                }
                $uuid_variable = Core::uuid_variable();
                $data = [];
                $data[] = 'try {';
                foreach($before as $before_record){
                    $data[] = $before_record;
                }
                $data[] = $uuid_variable . ' = ' . $method_value;
                foreach($after as $after_record){
                    $data[] = $after_record;
                }
                $data[] = 'if(!is_scalar('. $uuid_variable. ')){';
                $data[] = '//array or object';
                $data[] = '//nothing';
                $data[] = '}';
                $data[] = 'elseif(is_bool('. $uuid_variable. ') || is_null(' . $uuid_variable . ')){';
                $data[] = '//nothing';
                $data[] = '} else {';
                $data[] = 'echo '. $uuid_variable .';';
                $data[] = '}';
                $data[] = '}';
                $data[] = 'catch(LocateException | TemplateException | Exception | Error | ErrorException $exception){';
                if(
                    array_key_exists('is_multiline', $record) &&
                    $record['is_multiline'] === true
                ){
                    $data[] = 'ob_get_clean();';
//                    $data[] = 'breakpoint($exception);';
                    $data[] = 'throw new TemplateException(\'' . str_replace('\'', '\\\'', $record['tag']) . PHP_EOL . 'On line: ' . $record['line']['start']  . ', column: ' . $record['column'][$record['line']['start']]['start'] . ' in source: '. $source . '.' . '\' . PHP_EOL . (string) $exception);';
//                    $data[] = 'throw new TemplateException(\'' . str_replace('\'', '\\\'', $record['tag']) . PHP_EOL . 'On line: ' . $record['line']['start']  . ', column: ' . $record['column'][$record['line']['start']]['start'] . ' in source: '. $source . '.' . '\');';
                } else {
                    $data[] = 'ob_get_clean();';
//                    $data[] = 'breakpoint($exception);';
                    $data[] = 'throw new TemplateException(\'' . str_replace('\'', '\\\'', $record['tag']) . PHP_EOL . 'On line: ' . $record['line']  . ', column: ' . $record['column']['start'] . ' in source: ' . $source . '.' . '\' . PHP_EOL . (string) $exception);';
//                    $data[] = 'throw new TemplateException(\'' . str_replace('\'', '\\\'', $record['tag']) . PHP_EOL . 'On line: ' . $record['line']  . ', column: ' . $record['column']['start'] . ' in source: ' . $source . '.' . '\');';
                }
                $data[] = '}';
                return implode(PHP_EOL, $data);
            break;
        }
        $data = [];
        foreach($before as $before_record){
            $data[] = $before_record;
        }
        $data[] = $method_value;
        foreach($after as $after_record){
            $data[] = $after_record;
        }
        return implode(PHP_EOL, $data);
    }

    /**
     * @throws Exception
     * @throws LocateException
     * @throws TemplateException
     */
    public static function variable_assign(App $object, $flags, $options, $record = []): bool | string
    {
        if(!array_key_exists('variable', $record)){
            return false;
        }
        elseif(
            !array_key_exists('is_assign', $record['variable']) ||
            $record['variable']['is_assign'] !== true
        ) {
            return false;
        }
        $source = $options->source ?? '';
        $variable_name = $record['variable']['name'];
        $operator = $record['variable']['operator'];
        $before = [];
        $before_value = [];
        $after_value = [];
        if(
            in_array(
                $operator,
                [
                    '++',
                    '--',
                    '**'
                ],
                true
            )
        ){
            $value = ''; //init ++, --, **
        }
        elseif(
            array_key_exists('value', $record['variable']) &&
            is_array($record['variable']['value']) &&
            array_key_exists('array', $record['variable']['value']) &&
            is_array($record['variable']['value']['array']) &&
            array_key_exists(0, $record['variable']['value']['array']) &&
            is_array($record['variable']['value']['array'][0]) &&
            array_key_exists('is_class_method', $record['variable']['value']['array'][0]) &&
            $record['variable']['value']['array'][0]['is_class_method'] === true
        ){
            //static class method call
//            breakpoint($record);
            $method = $record['variable']['value']['array'][0]['method']['name'] ?? null;
            $method = str_replace('.', '_', $method);
            $explode = explode('::', $method);
            $function = array_pop($explode);
            $method = implode('\\', $explode);
            if(array_key_exists(1, $explode) && $explode[0] !== ''){
                $method = '\\' . $method;
            }
            $class_name = $method;
            $method .= '::' . $function;
            $uuid = Core::uuid_variable();
            $uuid_methods = Core::uuid_variable();
            $argument = $record['variable']['value']['array'][0]['method']['argument'] ?? [];
            foreach($argument as $argument_nr => $argument_record){
                $value = Build::value($object, $flags, $options, $record, $argument_record, $is_set);
                $argument[$argument_nr] = $value;
            }
            $use_class = $object->config('package.raxon/parse.build.use.class');
            foreach($use_class as $use_as){
                $explode = explode('as', $use_as);
                if(array_key_exists(1, $explode)){
                    $use_class_name = trim($explode[1]);
                } else {
                    $explode = explode('\\', $use_as);
                    $use_class_name = array_pop($explode);
                }
                if($use_class_name === $class_name){
                    $class_name = $use_as;
                    break;
                }
            }
            $before[] = 'try {';
            $before[] = $uuid . ' = new ReflectionClass(\'' . $class_name . '\');';
            $before[] = $uuid_methods . ' = ' . $uuid . '->getMethods();';
            $before[] = 'foreach (' . $uuid_methods . ' as $nr => $method) {';
            $before[] = 'if ($method->isStatic()) {';
            $before[] = $uuid_methods . '[$nr] = $method->name;';
            $before[] = '} else {';
            $before[] = 'unset(' . $uuid_methods . '[$nr]);';
            $before[] = '}';
            $before[] = '}';
//            $before[] = 'd( ' . $uuid_methods . ');';
            $before[] = 'if(!in_array(\'' . $function . '\', ' . $uuid_methods. ', true)){';
            $before[] = 'sort(' . $uuid_methods .', SORT_NATURAL);';
            $before[] = 'ob_get_clean();';
            $before[] = 'throw new TemplateException(\'Static method "' . $function . '" not found in class: ' . $class_name . '\' . PHP_EOL . \'Available static methods:\' . PHP_EOL . implode(PHP_EOL, ' . $uuid_methods . ') . PHP_EOL);';
            $before[] = '}';
            $before[] = '}';
            $before[] = 'catch(Exception | LocateException $exception){';
            if(
                array_key_exists('is_multiline', $record) &&
                $record['is_multiline'] === true
            ){
                $before[] = 'ob_get_clean();';
                $before[] = 'throw new TemplateException(\'' . str_replace('\'', '\\\'', $record['tag']) . PHP_EOL . 'On line: ' . $record['line']['start']  . ', column: ' . $record['column'][$record['line']['start']]['start'] . ' in source: ' . $source . '.\', 0, $exception);';
            } else {
                $before[] = 'ob_get_clean();';
                $before[] = 'throw new TemplateException(\'' . str_replace('\'', '\\\'', $record['tag']) . PHP_EOL . 'On line: ' . $record['line']  . ', column: ' . $record['column']['start'] . ' in source: ' . $source . '.\', 0, $exception);';
            }
            $before[] = '}';
            if(array_key_exists(0, $argument)){
                $value = $method . '(' . implode(', ', $argument) . ')';
            } else {
                $value = $method . '()';
            }
        }
        elseif(
            array_key_exists('value', $record['variable']) &&
            is_array($record['variable']['value']) &&
            array_key_exists('array', $record['variable']['value']) &&
            is_array($record['variable']['value']['array']) &&
            array_key_exists(0, $record['variable']['value']['array']) &&
            is_array($record['variable']['value']['array'][0]) &&
            array_key_exists('value', $record['variable']['value']['array'][0]) &&
            $record['variable']['value']['array'][0]['value'] === '$' &&
            array_key_exists(1, $record['variable']['value']['array']) &&
            is_array($record['variable']['value']['array'][1]) &&
            array_key_exists('method', $record['variable']['value']['array'][1]) &&
            array_key_exists('name', $record['variable']['value']['array'][1]['method'])
        ){
            //class method call
//            breakpoint($record);
            $method = $record['variable']['value']['array'][1]['method']['name'] ?? null;
            $explode = explode('.', $method, 2);
            //replace : with \\ for namespace in $explode[0]
            $class_raw = $explode[0];
            $class_name = str_replace(':', '\\', $class_raw);
            $class_object = '$' . $class_name;
            $class_method = str_replace('.', '_', $explode[1]);
            $uuid = Core::uuid_variable();
            $uuid_methods = Core::uuid_variable();
            $argument = $record['variable']['value']['array'][1]['method']['argument'];
            foreach($argument as $argument_nr => $argument_record){
                $value = Build::value($object, $flags, $options, $record, $argument_record, $is_set);
                $argument[$argument_nr] = $value;
            }
            $before[] = 'try {';
            $before[] = $uuid . ' = $data->get(\'' . $class_name . '\');';
            $before[] = $uuid_methods . ' = get_class_methods(' . $uuid . ');';
            $before[] = 'if(!in_array(\'' . $class_method . '\', ' . $uuid_methods. ', true)){';
            $before[] = 'sort(' . $uuid_methods .', SORT_NATURAL);';
            $before[] = 'ob_get_clean();';
            $before[] = 'throw new TemplateException(\'Method "' . $class_method . '" not found in class: ' . $class_raw . '\' . PHP_EOL . \'Available methods:\' . PHP_EOL . implode(PHP_EOL, ' . $uuid_methods . ') . PHP_EOL);';
            $before[] = '}';
            $before[] = '}';
            $before[] = 'catch(Exception | TemplateException $exception){';
            if(
                array_key_exists('is_multiline', $record) &&
                $record['is_multiline'] === true
            ){
                $before[] = 'ob_get_clean();';
                $before[] = 'throw new TemplateException(\'' . str_replace('\'', '\\\'', $record['tag']) . PHP_EOL . 'On line: ' . $record['line']['start']  . ', column: ' . $record['column'][$record['line']['start']]['start'] . ' in source: ' . $source . '.\', 0, $exception);';
            } else {
                $before[] = 'ob_get_clean();';
                $before[] = 'throw new TemplateException(\'' . str_replace('\'', '\\\'', $record['tag'])  . PHP_EOL . 'On line: ' . $record['line']  . ', column: ' . $record['column']['start'] . ' in source: ' . $source . '.\', 0, $exception);';
            }
            $before[] = '}';
            if(array_key_exists(0, $argument)){
                $value = $uuid . '->' . $class_method .  '(' . implode(', ', $argument) . ')';
            } else {
                $value = $uuid . '->' . $class_method . '()';
            }
        }
        elseif(
            array_key_exists('value', $record['variable']) &&
            is_array($record['variable']['value']) &&
            array_key_exists('array', $record['variable']['value']) &&
            is_array($record['variable']['value']['array']) &&
            array_key_exists(0, $record['variable']['value']['array']) &&
            is_array($record['variable']['value']['array'][0]) &&
            array_key_exists('type', $record['variable']['value']['array'][0]) &&
            array_key_exists(1, $record['variable']['value']['array']) &&
            is_array($record['variable']['value']['array'][1]) &&
            array_key_exists('value', $record['variable']['value']['array'][1]) &&
            array_key_exists(2, $record['variable']['value']['array']) &&
            is_array($record['variable']['value']['array'][2]) &&
            array_key_exists('type', $record['variable']['value']['array'][2]) &&
            $record['variable']['value']['array'][0]['type'] === 'string' &&
            $record['variable']['value']['array'][1]['value'] === '::' &&
            $record['variable']['value']['array'][2]['type'] === 'method'
        ){
            //static method call
            $name = $record['variable']['value']['array'][0]['value'];
            $name .= $record['variable']['value']['array'][1]['value'];
            $class_static = Build::class_static($object);
            if(
                in_array(
                    $name,
                    $class_static,
                    true
                )
            ){
                $name .= $record['variable']['value']['array'][2]['method']['name'];
                $argument = $record['variable']['value']['array'][2]['method']['argument'];
                foreach($argument as $argument_nr => $argument_record){
                    $value = Build::value($object, $flags, $options, $record, $argument_record, $is_set);
                    $argument[$argument_nr] = $value;
                }
                if(array_key_exists(0, $argument)){
                    $value = $name . '(' . implode(', ', $argument) . ')';
                } else {
                    $value = $name . '()';
                }
            } else {
                if(
                    array_key_exists('is_multiline', $record) &&
                    $record['is_multiline'] === true
                ){
                    throw new TemplateException(
                        $record['tag'] . PHP_EOL .
                        'Unknown static class call "{{' . $name .'}}" please add the class usage on line: ' .
                        $record['line']['start']  .
                        ', column: ' .
                        $record['column'][$record['line']['start']]['start'] .
                        ' in source: '.
                        $source,
                    );

                } else {
                    throw new TemplateException(
                        $record['tag'] . PHP_EOL .
                        'Unknown static class call "{{' . $name .'}}" please add the class usage on line: ' .
                        $record['line'] .
                        ', column: ' .
                        $record['column']['start'] .
                        ' in source: '.
                        $source,
                    );
                }
            }
        } else {
            $value = Build::value($object, $flags, $options, $record, $record['variable']['value'],$is_set, $before_value, $after_value);
        }
        if(array_key_exists('modifier', $record['variable'])){
            d($value);
            ddd('what happens with value');
            $previous_modifier = '$data->get(\'' . $record['variable']['name'] . '\')';
            foreach($record['variable']['modifier'] as $nr => $modifier){
                $plugin = Build::plugin($object, $flags, $options, $record, str_replace('.', '_', $modifier['name']));
                $modifier_value = '$this->' . $plugin . '(';
                $modifier_value .= $previous_modifier .', ';
                if(array_key_exists('argument', $modifier)){
                    $is_argument = false;
                    foreach($modifier['argument'] as $argument_nr => $argument){
                        $argument = Build::value($object, $flags, $options, $record, $argument, $is_set);
                        if($argument !== ''){
                            $modifier_value .= $argument . ', ';
                            $is_argument = true;
                        }
                    }
                    if($is_argument === true){
                        $modifier_value = mb_substr($modifier_value, 0, -2);
                    } else {
                        $modifier_value = mb_substr($modifier_value, 0, -1);
                    }
                }
                $modifier_value .=  ')';
                $previous_modifier = $modifier_value;
            }
            $value = $modifier_value;
        }
        if(
            $variable_name !== '' &&
            $operator !== ''
        ){
            $result = $before;
            if($value !== ''){
                $result[] = 'try {';
                foreach($before_value as $before_record){
                    $result[] = $before_record;
                }
                switch($operator){
                    case '=' :
                        $result[] = '$data->set(' .
                            '\'' .
                            $variable_name .
                            '\', ' .
                            $value .
                            ');'
                        ;
                        foreach($after_value as $after_record){
                            if(!is_array($after_record)){
                                $result[] = $after_record;
                            }
                        }
                        $result[] = '} catch(ErrorException | Error | Exception $exception){';
                        if(
                            array_key_exists('is_multiline', $record) &&
                            $record['is_multiline'] === true
                        ){
                            $result[] = 'ob_get_clean();';
                            $result[] = 'throw new TemplateException(\'' . str_replace('\'', '\\\'', $record['tag']) . PHP_EOL . 'On line: ' . $record['line']['start']  . ', column: ' . $record['column'][$record['line']['start']]['start'] . ' in source: '. $source . '\', 0, $exception);';
                        } else {
                            $result[] = 'ob_get_clean();';
                            $result[] = 'throw new TemplateException(\'' . str_replace('\'', '\\\'', $record['tag']) . PHP_EOL . 'On line: ' . $record['line']  . ', column: ' . $record['column']['start'] . ' in source: ' . $source . '\', 0, $exception);';
                        }
                        $result[] = '}';
                        break;
                    case '.=' :
                        $result[] = '$data->set(' .
                            '\'' .
                            $variable_name .
                            '\', ' .
                            '$this->value_concatenate(' .
                            '$data->get(' .
                            '\'' .
                            $variable_name .
                            '\'), ' .
                            $value .
                            ')' .
                            ');'
                        ;
                        foreach($after_value as $after_record){
                            if(!is_array($after_record)){
                                $result[] = $after_record;
                            }
                        }
                        $result[] = '} catch(ErrorException | Error | Exception $exception){';
                        if(
                            array_key_exists('is_multiline', $record) &&
                            $record['is_multiline'] === true
                        ){
                            $result[] = 'ob_get_clean();';
                            $result[] = 'throw new TemplateException(\'' . str_replace('\'', '\\\'', $record['tag']) . PHP_EOL . 'On line: ' . $record['line']['start']  . ', column: ' . $record['column'][$record['line']['start']]['start'] . ' in source: '. $source . '\', 0, $exception);';
                        } else {
                            $result[] = 'ob_get_clean();';
                            $result[] = 'throw new TemplateException(\'' . str_replace('\'', '\\\'', $record['tag']) . PHP_EOL . 'On line: ' . $record['line']  . ', column: ' . $record['column']['start'] . ' in source: ' . $source . '\', 0, $exception);';
                        }
                        $result[] = '}';
                        break;
                    case '+=' :
                        $result[] = '$data->set(' .
                            '\'' .
                            $variable_name .
                            '\', ' .
                            '$this->value_plus('.
                            '$data->get('.
                            '\'' .
                            $variable_name .
                            '\'), ' .
                            $value .
                            ')' .
                            ');'
                        ;
                        foreach($after_value as $after_record){
                            if(!is_array($after_record)){
                                $result[] = $after_record;
                            }
                        }
                        $result[] = '} catch(ErrorException | Error | Exception $exception){';
                        if(
                            array_key_exists('is_multiline', $record) &&
                            $record['is_multiline'] === true
                        ){
                            $result[] = 'ob_get_clean();';
                            $result[] = 'throw new TemplateException(\'' . str_replace('\'', '\\\'', $record['tag']) . PHP_EOL . 'On line: ' . $record['line']['start']  . ', column: ' . $record['column'][$record['line']['start']]['start'] . ' in source: '. $source . '\', 0, $exception);';
                        } else {
                            $result[] = 'ob_get_clean();';
                            $result[] = 'throw new TemplateException(\'' . str_replace('\'', '\\\'', $record['tag']) . PHP_EOL . 'On line: ' . $record['line']  . ', column: ' . $record['column']['start'] . ' in source: ' . $source . '\', 0, $exception);';
                        }
                        $result[] = '}';
                        break;
                    case '-=' :
                        $result[] = '$data->set('.
                            '\'' .
                            $variable_name .
                            '\', ' .
                            '$this->value_minus('.
                            '$data->get('.
                            '\'' .
                            $variable_name .
                            '\'), ' .
                            $value .
                            ')'.
                            ');'
                        ;
                        foreach($after_value as $after_record){
                            if(!is_array($after_record)){
                                $result[] = $after_record;
                            }
                        }
                        $result[] = '} catch(ErrorException | Error | Exception $exception){';
                        if(
                            array_key_exists('is_multiline', $record) &&
                            $record['is_multiline'] === true
                        ){
                            $result[] = 'ob_get_clean();';
                            $result[] = 'throw new TemplateException(\'' . str_replace('\'', '\\\'', $record['tag']) . PHP_EOL . 'On line: ' . $record['line']['start']  . ', column: ' . $record['column'][$record['line']['start']]['start'] . ' in source: '. $source . '\', 0, $exception);';
                        } else {
                            $result[] = 'ob_get_clean();';
                            $result[] = 'throw new TemplateException(\'' . str_replace('\'', '\\\'', $record['tag']) . PHP_EOL . 'On line: ' . $record['line']  . ', column: ' . $record['column']['start'] . ' in source: ' . $source . '\', 0, $exception);';
                        }
                        $result[] = '}';
                        break;
                    case '*=' :
                        $result[] = '$data->set('.
                            '\'' .
                            $variable_name .
                            '\', ' .
                            '$this->value_multiply('.
                            '$data->get('.
                            '\'' .
                            $variable_name .
                            '\'), ' .
                            $value .
                            ')'.
                            ');'
                        ;
                        foreach($after_value as $after_record){
                            if(!is_array($after_record)){
                                $result[] = $after_record;
                            }
                        }
                        $result[] = '} catch(ErrorException | Error | Exception $exception){';
                        if(
                            array_key_exists('is_multiline', $record) &&
                            $record['is_multiline'] === true
                        ){
                            $result[] = 'ob_get_clean();';
                            $result[] = 'throw new TemplateException(\'' . str_replace('\'', '\\\'', $record['tag']) . PHP_EOL . 'On line: ' . $record['line']['start']  . ', column: ' . $record['column'][$record['line']['start']]['start'] . ' in source: '. $source . '\', 0, $exception);';
                        } else {
                            $result[] = 'ob_get_clean();';
                            $result[] = 'throw new TemplateException(\'' . str_replace('\'', '\\\'', $record['tag']) . PHP_EOL . 'On line: ' . $record['line']  . ', column: ' . $record['column']['start'] . ' in source: ' . $source . '\', 0, $exception);';
                        }
                        $result[] = '}';
                        break;
                }
                $result = implode(PHP_EOL, $result);
            } else {
                switch($operator){
                    case '++' :
                        $result = '$data->set(\'' . $variable_name . '\', ' .  '$this->value_plus_plus($data->get(\'' . $variable_name . '\')));';
                    break;
                    case '--' :
                        $result = '$data->set(\'' . $variable_name . '\', ' .  '$this->value_minus_minus($data->get(\'' . $variable_name . '\')));';
                    break;
                    case '**' :
                        $result = '$data->set(\'' . $variable_name . '\', ' .  '$this->value_multiply_multiply($data->get(\'' . $variable_name . '\')));';
                    break;
                }
            }
            try {
                Validator::validate($object, $flags, $options, $result);
            }
            catch(Exception $exception){
                if(
                    array_key_exists('is_multiline', $record) &&
                    $record['is_multiline'] === true
                ){
                    throw new TemplateException($record['tag'] .  PHP_EOL . 'On line: ' . $record['line']['start']  . ', column: ' . $record['column'][$record['line']['start']]['start'] . ' in source: '. $source . '.', 0, $exception);
                } else {
                    throw new TemplateException($record['tag'] . PHP_EOL . 'On line: ' . $record['line']  . ', column: ' . $record['column']['start'] . ' in source: ' . $source . '.', 0, $exception);
                }
            }
            return $result;
        }
        return false;
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

    public static function align_content(App $object, $flags, $options, $input, $indent): string
    {
        $list = Build::string_array($input);
        foreach($list as $nr => $line){
            $list[$nr] = str_repeat(' ', $indent * 4) . $line;
        }
        return implode(PHP_EOL, $list);
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
                $input['array'][$is_single_quote]['execute'] = $input['array'][$is_single_quote]['value'];
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

    /**
     * @throws Exception
     * @throws LocateException
     */
    public static function value(App $object, $flags, $options, $tag, $input, &$is_set=false, &$before=[], &$after=[]): string
    {
        $source = $options->source ?? '';
        $value = '';
        $skip = 0;
        $input = Build::value_single_quote($object, $flags, $options, $input);
        $input = Build::value_set($object, $flags, $options, $input, $is_set);
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
                $value .= $record['execute'];
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
                                $assign = Build::value_right(
                                    $object,
                                    $flags,
                                    $options,
                                    $input,
                                    $nr,
                                    $next,
                                    $skip
                                );
                                $assign = Build::value($object, $flags, $options, $tag, $assign, $is_set);
                                $value .= '$data->set(\'' . $previous['name'] . '\', value_concatenate($data->get(\'' . $previous['name'] .'\', ' .  $assign . ')';
                                break;
                            case '+=':
                                $assign = Build::value_right(
                                    $object,
                                    $flags,
                                    $options,
                                    $input,
                                    $nr,
                                    $next,
                                    $skip
                                );
                                $assign = Build::value($object, $flags, $options, $tag, $assign, $is_set);
                                $value .= '$data->set(\'' . $previous['name'] . '\', value_plus($data->get(\'' . $previous['name'] .'\', ' .  $assign . ')';
                            break;
                            case '-=':
                                $assign = Build::value_right(
                                    $object,
                                    $flags,
                                    $options,
                                    $input,
                                    $nr,
                                    $next,
                                    $skip
                                );
                                $assign = Build::value($object, $flags, $options, $tag, $assign, $is_set);
                                $value .= '$data->set(\'' . $previous['name'] . '\', value_minus($data->get(\'' . $previous['name'] .'\', ' .  $assign . ')';
                            break;
                            case '*=':
                                $assign = Build::value_right(
                                    $object,
                                    $flags,
                                    $options,
                                    $input,
                                    $nr,
                                    $next,
                                    $skip
                                );
                                $assign = Build::value($object, $flags, $options, $tag, $assign, $is_set);
                                $value .= '$data->set(\'' . $previous['name'] . '\', value_multiply($data->get(\'' . $previous['name'] .'\', ' .  $assign . ')';
                            break;
                            case '=':
                                $assign = Build::value_right(
                                    $object,
                                    $flags,
                                    $options,
                                    $input,
                                    $nr,
                                    $next,
                                    $skip
                                );
                                $assign = Build::value($object, $flags, $options, $tag, $assign, $is_set);
                                $value .= '$data->set(\'' . $previous['name'] . '\', ' .  $assign . ')';
                            break;
                            case '++' :
                                $value = '$data->set(\'' . $previous['name'] . '\', ' .  '$this->value_plus_plus($data->get(\'' . $previous['name'] . '\')))';
                            break;
                            case '--' :
                                $value = '$data->set(\'' . $previous['name'] . '\', ' .  '$this->value_minus_minus($data->get(\'' . $previous['name'] . '\')))';
                            break;
                            case '**' :
                                $value = '$data->set(\'' . $previous['name'] . '\', ' .  '$this->value_multiply_multiply($data->get(\'' . $previous['name'] . '\')))';
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
                    $right = Build::value_right(
                        $object,
                        $flags,
                        $options,
                        $input,
                        $nr,
                        $next,
                        $skip
                    );
                    $right = Build::value($object, $flags, $options, $tag, $right, $is_set);
                    if(array_key_exists('value', $record)){
                        $value = Build::value_calculate($object, $flags, $options, $record['value'], $value, $right);
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
                $array_value = Build::value($object, $flags, $options, $tag, $record, $is_set);
                $data = Build::string_array($array_value);
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
                $set_value .= Build::value($object, $flags, $options, $tag, $record, $is_set) . PHP_EOL;
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
                        $class_static = Build::class_static($object);
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
                    $method_value .= Build::argument($object, $flags, $options, $record, $before, $after);
                    $method_value .= ')';
                } else {
                    $plugin = Build::plugin($object, $flags, $options, $tag, str_replace('.', '_', $record['method']['name']));
                    $method_value = '$this->' . $plugin . '(' . PHP_EOL;
                    $method_value .= Build::argument($object, $flags, $options, $record, $before, $after);
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
                    $previous_modifier = '$data->get(\'' . $record['name'] . '\')';
                    //add method and arguments

                    foreach($record['modifier'] as $modifier_nr => $modifier){
                        $plugin = Build::plugin($object, $flags, $options, $tag, str_replace('.', '_', $modifier['name']));
                        if($is_single_line){
                            $modifier_value = '$this->' . $plugin . '( ' ;
                            $modifier_value .= $previous_modifier . ', ';
                        } else {
                            $modifier_value = '$this->' . $plugin . '(';
                            $modifier_value .= $previous_modifier . ', ';
                        }
                        $is_argument = false;
                        if(array_key_exists('argument', $modifier)){
                            foreach($modifier['argument'] as $argument_nr => $argument){
                                if($is_single_line){
                                    $argument = Build::value($object, $flags, $options, $tag, $argument, $is_set);
                                    if($argument !== ''){
                                        $modifier_value .= $argument . ', ';
                                        $is_argument = true;
                                    }
                                } else {
                                    $argument = Build::value($object, $flags, $options, $tag, $argument, $is_set);
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
                            $argument = Build::value($object, $flags, $options, $tag, $argument, $is_set);
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
                    $value .= '$data->get(\'' . $record['variable']['name'] . '\')' . $method_value;
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
                            $variable_value = Build::value($object, $flags, $options, $tag, $record['variable']['value'], $is_set);
                            $value .= '$data->set(\'' . $record['variable']['name'] . '\', ' . $variable_value . ')';
                        break;
                        case '.=':
                            $variable_value = Build::value($object, $flags, $options, $tag, $record['variable']['value'], $is_set);
                            $value .= '$data->set(\'' . $record['variable']['name'] . '\', ' .  '$this->value_concatenate($data->get(\'' . $record['variable']['name'] . '\'), ' .  $variable_value . '))';
                        break;
                        case '+=':
                            $variable_value = Build::value($object, $flags, $options, $tag, $record['variable']['value'], $is_set);
                            $value .= '$data->set(\'' . $record['variable']['name'] . '\', ' .  '$this->value_plus($data->get(\'' . $record['variable']['name'] . '\'), ' .  $variable_value . '))';
                        break;
                        case '-=':
                            $variable_value = Build::value($object, $flags, $options, $tag, $record['variable']['value'], $is_set);
                            $value .= '$data->set(\'' . $record['variable']['name'] . '\', ' .  '$this->value_minus($data->get(\'' . $record['variable']['name'] . '\'), ' .  $variable_value . '))';
                        break;
                        case '*=':
                            $variable_value = Build::value($object, $flags, $options, $tag, $record['variable']['value'], $is_set);
                            $value .= '$data->set(\'' . $record['variable']['name'] . '\', ' .  '$this->value_multiply($data->get(\'' . $record['variable']['name'] . '\'), ' .  $variable_value . '))';
                            break;
                        case '++':
                            $value .= '$data->set(\'' . $record['variable']['name'] . '\', ' .  '$this->value_plus_plus($data->get(\'' . $record['variable']['name'] . '\')))';
                        break;
                        case '--':
                            $value .= '$data->set(\'' . $record['variable']['name'] . '\', ' .  '$this->value_minus_minus($data->get(\'' . $record['variable']['name'] . '\')))';
                        break;
                        case '**':
                            $value .= '$data->set(\'' . $record['variable']['name'] . '\', ' .  '$this->value_multiply_multiply($data->get(\'' . $record['variable']['name'] . '\')))';
                        break;
                        default:
                            breakpoint($record);
                            throw new Exception('Not implemented...');
                    }
                } else {
                    $modifier_value = '';
                    if(array_key_exists('modifier', $record)){
                        $previous_modifier = '$data->get(\'' . $record['name'] . '\')';
                        $after[] = [
                            'attribute' => $record['name']
                        ];
                        foreach($record['modifier'] as $modifier_nr => $modifier){
                            $plugin = Build::plugin($object, $flags, $options, $tag, str_replace('.', '_', $modifier['name']));
                            if($is_single_line){
                                $modifier_value = '$this->' . $plugin . '(';
                                $modifier_value .= $previous_modifier . ', ';
                            } else {
                                $modifier_value = '$this->' . $plugin . '(';
                                $modifier_value .= $previous_modifier . ', ';
                            }
                            $is_argument = false;
                            if(array_key_exists('argument', $modifier)){
                                foreach($modifier['argument'] as $argument_nr => $argument){
                                    if($is_single_line){
                                        $argument = Build::value($object, $flags, $options, $tag, $argument, $is_set);
                                        if($argument !== ''){
                                            $modifier_value .= $argument . ', ';
                                            $is_argument = true;
                                        }
                                    } else {
                                        $argument = Build::value($object, $flags, $options, $tag, $argument, $is_set);
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
                        $value .= '$data->get(\'' . $record['name'] . '\')';
                        $after[] = [
                            'attribute' => $record['name']
                        ];
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
                $right = Build::value_right(
                    $object,
                    $flags,
                    $options,
                    $input,
                    $nr,
                    $next,
                    $skip
                );
                $right = Build::value($object, $flags, $options, $tag, $right, $is_set);
                if(array_key_exists('value', $record)){
                    $value = Build::value_calculate($object, $flags, $options, $record['value'], $value, $right);
                }
            }
        }
        return $value;
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
}