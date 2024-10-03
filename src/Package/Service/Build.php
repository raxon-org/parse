<?php
namespace Package\Raxon\Parse\Service;

use Raxon\App;

use Raxon\Module\Autoload;
use Raxon\Module\Core;
use Raxon\Module\File;

use Plugin;
use Exception;

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
        foreach($tags as $row_nr => $list){
            foreach($list as $nr => &$record){
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
                                        $record['tag'] . PHP_EOL .
                                        'Unused foreach close tag "{{/foreach}}" on line: ' .
                                        $record['line']['start'] .
                                        ', column: ' .
                                        $record['column'][$record['line']['start']]['start'] .
                                        ' in source: ' .
                                        $source,
                                    );

                                } else {
                                    throw new TemplateException(
                                        $record['tag'] . PHP_EOL .
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
                                        $record['tag'] . PHP_EOL .
                                        'Unused while close tag "{{/while}}" on line: ' .
                                        $record['line']['start'] .
                                        ', column: ' .
                                        $record['column'][$record['line']['start']]['start'] .
                                        ' in source: ' .
                                        $source,
                                    );
                                } else {
                                    throw new TemplateException(
                                        $record['tag'] . PHP_EOL .
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
                                        $record['tag'] . PHP_EOL .
                                        'Unused for close tag "{{/for}}" on line: ' .
                                        $record['line']['start'] .
                                        ', column: ' .
                                        $record['column'][$record['line']['start']]['start'] .
                                        ' in source: ' .
                                        $source,
                                    );
                                } else {
                                    throw new TemplateException(
                                        $record['tag'] . PHP_EOL .
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
                                        $record['tag'] . PHP_EOL .
                                        'Unused if close tag "{{/if}}" on line: ' .
                                        $record['line']['start'] .
                                        ', column: ' .
                                        $record['column'][$record['line']['start']]['start'] .
                                        ' in source: ' .
                                        $source,
                                    );

                                } else {
                                    throw new TemplateException(
                                        $record['tag'] . PHP_EOL .
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
                                    $value = Build::value($object, $flags, $options, $record, $argument_record);
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
                    if(
                        array_key_exists('is_multiline', $record) &&
                        $record['is_multiline'] === true
                    ){
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

    public static function document_run(App $object, $flags, $options, $document = [], $data = []): array
    {
        $build = new Build($object, $flags, $options);
        $indent = $object->config('package.raxon/parse.build.state.indent');
        $document[] = str_repeat(' ', $indent * 4) . '/**';
        $document[] = str_repeat(' ', $indent * 4) . ' * @throws Exception';
        $document[] = str_repeat(' ', $indent * 4) . ' */';
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
        $document[] = str_repeat(' ', $indent * 4) . 'throw new Exception(\'$object is not an instance of Raxon\App\');';
        $indent--;
        $document[] = str_repeat(' ', $indent * 4) . '}';
        $document[] = str_repeat(' ', $indent * 4) . 'if (!($parse instanceof Parse)) {';
        $indent++;
        $document[] = str_repeat(' ', $indent * 4) . 'throw new Exception(\'$parse is not an instance of Package\Raxon\Parse\Service\Parse\');';
        $indent--;
        $document[] = str_repeat(' ', $indent * 4) . '}';
        $document[] = str_repeat(' ', $indent * 4) . 'if (!($data instanceof Data)) {';
        $indent++;
        $document[] = str_repeat(' ', $indent * 4) . 'throw new Exception(\'$data is not an instance of Raxon\Module\Data\');';
        $indent--;
        $document[] = str_repeat(' ', $indent * 4) . '}';
        $document[] = str_repeat(' ', $indent * 4) . 'if (!is_object($flags)) {';
        $indent++;
        $document[] = str_repeat(' ', $indent * 4) . 'throw new Exception(\'$flags is not an object\');';
        $indent--;
        $document[] = str_repeat(' ', $indent * 4) . '}';
        $document[] = str_repeat(' ', $indent * 4) . 'if (!is_object($options)) {';
        $indent++;
        $document[] = str_repeat(' ', $indent * 4) . 'throw new Exception(\'$options is not an object\');';
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
            foreach($data as $nr => $char){
                $previous = $data[$nr - 1] ?? null;
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
                    'require'
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

        $use_plugin = explode('_', $plugin);
        foreach($use_plugin as $nr => $use){
            $use_plugin[$nr] = ucfirst($use);
        }
        $use_plugin = 'Plugin\\' . implode('_', $use_plugin);

        $use = $object->config('package.raxon/parse.build.use.trait');
        if(!$use){
            $use = [];
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
                ],
                true
            )
        ){
            if(!in_array($use_plugin, $use, true)){
                $autoload = $object->data(App::AUTOLOAD_DIFFERENCE);
                $location = $autoload->locate($use_plugin, false,  Autoload::MODE_LOCATION);
                $exist = false;
                $locate_exception = [];
                foreach($location  as $nr => $fileList){
                    foreach($fileList as $file){
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
                        throw new LocateException(
                            'Plugin not found (' .
                            str_replace('_', '.', $name) .
                            ') exception: "' .
                            $record['tag'] .
                            '" on line: ' .
                            $record['line']['start']  .
                            ', column: ' .
                            $record['column'][$record['line']['start']]['start'] .
                            ' in source: '.
                            $source,
                            $locate_exception
                        );

                    } else {
                        throw new LocateException(
                            'Plugin not found (' .
                            str_replace('_', '.', $name) .
                            ') exception: "' .
                            $record['tag'] .
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
            }
        }
        $object->config('package.raxon/parse.build.use.trait', $use);
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
        if(array_key_exists('modifier', $record['variable'])){
            $previous_modifier = '$data->get(\'' . $variable_name . '\')';
            foreach($record['variable']['modifier'] as $nr => $modifier){
                $plugin = Build::plugin($object, $flags, $options, $record, str_replace('.', '_', $modifier['name']));
                $modifier_value = '$this->' . $plugin . '(' . PHP_EOL;
                $modifier_value .= $previous_modifier . ',' . PHP_EOL;
                $is_argument = false;
                if(array_key_exists('argument', $modifier)){
                    foreach($modifier['argument'] as $argument_nr => $argument){
                        $argument = Build::value($object, $flags, $options, $record, $argument);
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
            $data = [
                $variable_uuid . ' = ' . $value . ';',
            ];
            if(
                array_key_exists('is_multiline', $record) &&
                $record['is_multiline'] === true
            ){
                $data[] = 'if(' . $variable_uuid .' === null){';
//                $data[] = 'ddd($data);';
                $data[] = 'throw new TemplateException(\'Null-pointer exception: "$' . $variable_name . '" on line: ' . $record['line']['start']  . ', column: ' . $record['column'][$record['line']['start']]['start'] . ' in source: '. $source . '. You can use modifier "default" to surpress it \');';
                $data[] = '}';
            } else {
                $data[] = 'if(' . $variable_uuid .' === null){';
//                $data[] = 'ddd($data);';
                $data[] = 'throw new TemplateException(\'Null-pointer exception: "$' . $variable_name . '" on line: ' . $record['line']  . ', column: ' . $record['column']['start'] . ' in source: '. $source . '. You can use modifier "default" to surpress it \');';
                $data[] = '}';
            }
            $data[] = 'if(!is_scalar('. $variable_uuid. ')){';
            $data[] = '//array or object';
            $data[] = 'ob_get_clean();';
            $data[] = 'return ' . $variable_uuid .';';
            $data[] = '} else {';
            $data[] = 'echo '. $variable_uuid .';';
            $data[] = '}';
            return $data;
        } else {
            $data = [
                $variable_uuid . ' = $data->get(\'' . $variable_name . '\');' ,
            ];
            if(
                array_key_exists('is_multiline', $record) &&
                $record['is_multiline'] === true
            ){
                $data[] = 'if(' . $variable_uuid .' === null){';
//                $data[] = 'ddd($data);';
                $data[] = 'throw new TemplateException(\'Null-pointer exception: "$' . $variable_name . '" on line: ' . $record['line']['start']  . ', column: ' . $record['column'][$record['line']['start']]['start'] . ' in source: '. $source . '. You can use modifier "default" to surpress it \');';
                $data[] = '}';
            } else {
                $data[] = 'if(' . $variable_uuid .' === null){';
//                $data[] = 'ddd($data);';
                $data[] = 'throw new TemplateException(\'Null-pointer exception: "$' . $variable_name . '" on line: ' . $record['line']  . ', column: ' . $record['column']['start'] . ' in source: ' . $source . '. You can use modifier "default" to surpress it \');';
                $data[] = '}';
            }
            $data[] = 'if(!is_scalar('. $variable_uuid. ')){';
            $data[] = '//array or object';
            $data[] = 'ob_get_clean();';
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
     * @throws TemplateException
     */
    public static function method(App $object, $flags, $options, $record = []): bool | string
    {
        if(!array_key_exists('method', $record)){
            return false;
        }
        $source = $options->source ?? '';
        $method_name = mb_strtolower($record['method']['name']);
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
                    throw new TemplateException($record['tag'] . PHP_EOL . 'On line: ' . $record['line']['start']  . ', column: ' . $record['column'][$record['line']['start']]['start'] . ' in source: '. $source . '.');
                } else {
                    //invalid from
                    throw new TemplateException($record['tag'] . PHP_EOL . 'On line: ' . $record['line']  . ', column: ' . $record['column']['start'] . ' in source: ' . $source . '.');
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
                        throw new TemplateException($record['tag'] . PHP_EOL . 'On line: ' . $record['line']['start']  . ', column: ' . $record['column'][$record['line']['start']]['start'] . ' in source: '. $source . '.');
                    } else {
                        //invalid key
                        throw new TemplateException($record['tag'] . PHP_EOL . 'On line: ' . $record['line']  . ', column: ' . $record['column']['start'] . ' in source: ' . $source . '.');
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
                    throw new TemplateException($record['tag'] . PHP_EOL . 'On line: ' . $record['line']['start']  . ', column: ' . $record['column'][$record['line']['start']]['start'] . ' in source: '. $source . '.');
                } else {
                    //invalid value
                    throw new TemplateException($record['tag'] . PHP_EOL . 'On line: ' . $record['line']  . ', column: ' . $record['column']['start'] . ' in source: ' . $source . '.');
                }
                $foreach_from = Build::value($object, $flags, $options, $record, $value);
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
                    $method_value[] = 'throw new Exception(\'' . $record['tag'] . PHP_EOL . 'Invalid argument type: \' . $type . \' for foreach on line: ' . $record['line']['start']  . ', column: ' . $record['column'][$record['line']['start']]['start'] . ' in source: ' . $source . '\');';
                } else {
                    $method_value[] = 'throw new Exception(\'' . $record['tag'] . PHP_EOL . 'Invalid argument type: \' . $type . \' for foreach on line: ' . $record['line']  . ', column: ' . $record['column']['start'] . ' in source: ' . $source . '\');';
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
                    $value = Build::value($object, $flags, $options, $record, $argument);
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
            break;
            case 'for':
                $method_value[] = 'for(';
                $is_argument = false;
                $argument_count = count($record['method']['argument']);
                if($argument_count === 3){
                    foreach($record['method']['argument'] as $nr => $argument){
                        $value = Build::value($object, $flags, $options, $record, $argument);
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
                    $value = Build::value($object, $flags, $options, $record, $argument);
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
                                'break operator with non-integer operand is no longer supported...' .
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
                                'break operator with non-integer operand is no longer supported...' .
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
                        $value = Build::value($object, $flags, $options, $record, $record['method']['argument'][0]);
                        $is_argument = true;
                    }
                }
                if(
                    $is_argument === false ||
                    mb_strtolower($value) === 'null'
                ){
                    $method_value = 'break;';
                }
                elseif(
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
                } else {
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
            break;
            default:
                $plugin = Build::plugin($object, $flags, $options, $record, str_replace('.', '_', $record['method']['name']));
                $method_value = '$this->' . $plugin . '(';
                $is_argument = false;
                $argument_value = '';
                foreach($record['method']['argument'] as $nr => $argument) {
                    $argument = Build::value($object, $flags, $options, $record, $argument);
                    if($argument !== ''){
                        $argument_value .= $argument  . ', ';
                        $is_argument = true;
                    }
                }
                if($is_argument){
                    $argument_value = mb_substr($argument_value, 0, -2);
                    $method_value .= $argument_value;
                }
                $method_value .= ');';
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
                $data[] = $uuid_variable . ' = ' . $method_value;
                $data[] = 'if(is_scalar(' . $uuid_variable . ')){';
                $data[] = 'echo ' . $uuid_variable . ';';
                $data[] = '}';
                $data[] = '}';
                $data[] = 'catch(Exception $exception){';
                if(
                    array_key_exists('is_multiline', $record) &&
                    $record['is_multiline'] === true
                ){
                    $data[] = 'throw new TemplateException(\'' . str_replace('\'', '\\\'', $record['tag']) . PHP_EOL . 'On line: ' . $record['line']['start']  . ', column: ' . $record['column'][$record['line']['start']]['start'] . ' in source: '. $source . '.' . '\', 0, $exception);';
                } else {
                    $data[] = 'throw new TemplateException(\'' . str_replace('\'', '\\\'', $record['tag']) . PHP_EOL . 'On line: ' . $record['line']  . ', column: ' . $record['column']['start'] . ' in source: ' . $source . '.' . '\', 0, $exception);';
                }
                $data[] = '}';
                return implode(PHP_EOL, $data);
            break;
        }
        return $method_value;
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
        } else {
            $value = Build::value($object, $flags, $options, $record, $record['variable']['value']);
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
                        $argument = Build::value($object, $flags, $options, $record, $argument);
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
            $result = '';
            if($value !== ''){
                switch($operator){
                    case '=' :
                        $result = '$data->set(\'' .
                            $variable_name .
                            '\', ' .
                            $value .
                            ');'
                        ;
                        break;
                    case '.=' :
                        d('here');
                        $result = '$data->set(\'' .
                            $variable_name .
                            '\', ' .
                            '$this->value_concatenate($data->get(\'' .
                            $variable_name .
                            '\'), ' .
                            $value .
                            '));'
                        ;
                        break;
                    case '+=' :
                        $result = '$data->set(\'' .
                            $variable_name .
                            '\', ' .
                            '$this->value_plus($data->get(\'' .
                            $variable_name .
                            '\'), ' .
                            $value .
                            '));'
                        ;
                        break;
                    case '-=' :
                        $result = '$data->set(\'' . $variable_name . '\', ' .
                            '$this->value_minus($data->get(\'' .
                            $variable_name .
                            '\'), ' .
                            $value .
                            '));'
                        ;
                        break;
                    case '*=' :
                        $result = '$data->set(\'' .
                            $variable_name .
                            '\', ' .
                            '$this->value_multiply($data->get(\'' .
                            $variable_name .
                            '\'), ' .
                            $value .
                            '));'
                        ;
                        break;
                }

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
                    throw new TemplateException($record['tag'] . PHP_EOL . 'On line: ' . $record['line']['start']  . ', column: ' . $record['column'][$record['line']['start']]['start'] . ' in source: '. $source . '.', 0, $exception);
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

    /**
     * @throws Exception
     * @throws LocateException
     */
    public static function value(App $object, $flags, $options, $tag, $input): string
    {
        $source = $options->source ?? '';
        $value = '';
        $skip = 0;
        $input = Build::value_single_quote($object, $flags, $options, $input);
        $is_double_quote = false;
        $double_quote_previous = false;
        $is_cast = false;
        $is_clone = false;
        $is_single_line = false;
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
                        } else {
                            $value .= PHP_EOL . $record['value'];
                        }
                    } else {
                        $value .= $record['value'] . PHP_EOL;
                    }
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
                                $assign = Build::value($object, $flags, $options, $tag, $assign);
                                $value .= '$data->set(\'' . $previous['name'] . '\', ' .  $assign . ')';
                                d($value);
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
                    $right = Build::value($object, $flags, $options, $tag, $right);
                    d($right);
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
                $array_value = Build::value($object, $flags, $options, $tag, $record);
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
                $record['type'] === 'method'
            ){
                $plugin = Build::plugin($object, $flags, $options, $tag, str_replace('.', '_', $record['method']['name']));
                $method_value = '$this->' . $plugin . '(' . PHP_EOL;
                if(
                    array_key_exists('method', $record) &&
                    array_key_exists('argument', $record['method'])
                ){
                    $is_argument = false;
                    foreach($record['method']['argument'] as $argument_nr => $argument){
                        $argument = Build::value($object, $flags, $options, $tag, $argument);
                        if($argument !== ''){
                            $method_value .= $argument . ', ';
                            $is_argument = true;
                        }
                    }
                    if($is_argument === true){
                        $method_value = mb_substr($method_value, 0, -2);
                        $method_value .= ')';
                    } else {
                        $method_value = mb_substr($method_value, 0, -1);
                        $method_value .= ')';
                    }
                }
                $value .= $method_value;
            }
            elseif(
                array_key_exists('type', $record) &&
                $record['type'] === 'variable' &&
                $next !== '='
            ){
                $modifier_value = '';
                if(array_key_exists('modifier', $record)){
                    $previous_modifier = '$data->get(\'' . $record['name'] . '\')';
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
                                    $argument = Build::value($object, $flags, $options, $tag, $argument);
                                    if($argument !== ''){
                                        $modifier_value .= $argument . ', ';
                                        $is_argument = true;
                                    }
                                } else {
                                    $argument = Build::value($object, $flags, $options, $tag, $argument);
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
                $right = Build::value($object, $flags, $options, $tag, $right);
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
        d($current);
        d($left);
        d($right);
        switch($current){
            case 'true':
            case 'false':
            case 'null':
                $value = $current;
            break;
            case '.=':
            case '.':
                $value = '$this->value_concatenate(' . $left . ', ' . $right . ')';
            break;
            case '+':
                $value = '$this->value_plus(' . $left . ', ' . $right . ')';
            break;
            case '-':
                $value = '$this->value_minus(' . $left . ', ' . $right . ')';
            break;
            case '*':
                $value = '$this->value_multiply(' . $left . ', ' . $right . ')';
            break;
            case '%':
                $value = '$this->value_modulo(' . $left . ', ' . $right . ')';
            break;
            case '/':
                $value = '$this->value_divide(' . $left . ', ' . $right . ')';
            break;
            case '<':
                $value = '$this->value_smaller(' . $left . ', ' . $right . ')';
            break;
            case '<=':
                $value = '$this->value_smaller_equal(' . $left . ', ' . $right . ')';
            break;
            case '<<':
                $value = '$this->value_smaller_smaller(' . $left . ', ' . $right . ')';
            break;
            case '>':
                $value = '$this->value_greater(' . $left . ', ' . $right . ')';
            break;
            case '>=':
                $value = '$this->value_greater_equal(' . $left . ', ' . $right . ')';
            break;
            case '>>':
                $value = '$this->value_greater_greater(' . $left . ', ' . $right . ')';
            break;
            case '==':
                $value = '$this->value_equal(' . $left . ', ' . $right . ')';
            break;
            case '===':
                $value = '$this->value_identical(' . $left . ', ' . $right . ')';
            break;
            case '!=':
            case '<>':
                $value = '$this->value_not_equal(' . $left . ', ' . $right . ')';
            break;
            case '!==':
                $value = '$this->value_not_identical(' . $left . ', ' . $right . ')';
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
                for($i = $nr + 1; $i < $count; $i++){
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