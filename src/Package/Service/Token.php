<?php
namespace Package\Raxon\Parse\Service;

use Raxon\App;
use Raxon\Config;

use Raxon\Exception\ObjectException;
use Raxon\Module\Core;
use Raxon\Module\Data;
use Raxon\Module\Dir;
use Raxon\Module\File;

use Exception;
class Token
{

    /**
     * @throws Exception
     */
    public static function tokenize(App $object,$flags, $options,  $input=''): mixed
    {
        $start = microtime(true);
        $cache_url = false;
        $cache_dir = false;
        $tags = false;
        $hash = hash('sha256', 'token.' . $input);
        $cache_dir = $object->config('ramdisk.url') .
            $object->config(Config::POSIX_ID) .
            $object->config('ds') .
            'Parse' .
            $object->config('ds')
        ;
        $cache_url = $cache_dir . $hash . $object->config('extension.json');
        $mtime = false;
        if(property_exists($options, 'source')){
            $mtime = File::mtime($options->source);
        }
        $is_new = false;
        if(
            property_exists($options, 'ramdisk') &&
            $options->ramdisk === true
        ){
            if(
                property_exists($options, 'compress') &&
                $options->compress === true
            ){
                $cache_url .= '.gz';
                if(
                    File::exist($cache_url) &&
                    $mtime === File::mtime($cache_url)
                ){
                    $tags = File::read($cache_url);
                    $tags = gzdecode($tags);
                    $tags = Core::object($tags, Core::OBJECT_ARRAY);
                }
                elseif(File::exist($cache_url)){
                    File::delete($cache_url);
                }
            }
            elseif(
                File::exist($cache_url) &&
                $mtime === File::mtime($cache_url)
            ){
                $tags = File::read($cache_url);
                $tags = Core::object($tags, Core::OBJECT_ARRAY);
            }
            elseif(File::exist($cache_url)){
                File::delete($cache_url);
            }
        }
        if($tags === false){
            $tags = Tag::define($object, $flags, $options, $input);
            $tags = Tag::remove($object, $flags, $options, $tags);
            $tags = Token::abstract_syntax_tree($object, $flags, $options, $tags);
            $is_new = true;
        }
        if(
            property_exists($options, 'ramdisk') &&
            $options->ramdisk === true &&
            $cache_url &&
            $is_new === true
        ){
            Dir::create($cache_dir, Dir::CHMOD);
            if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
                if(
                    property_exists($options, 'compress') &&
                    $options->compress === true
                ){
                    $data = new Data($tags);
                    $data->write($cache_url, [
                        'compact' => true,
                        'compress' => true
                    ]);
                } else {
                    File::write($cache_url, Core::object($tags, Core::OBJECT_JSON));
                }
            } else {
                if(
                    property_exists($options, 'compress') &&
                    $options->compress === true
                ){
                    $data = new Data($tags);
                    $data->write($cache_url, [
                        'compact' => true,
                        'compress' => true
                    ]);
                } else {
                    File::write($cache_url, Core::object($tags, Core::OBJECT_JSON_LINE));
                }
            }
            File::touch($cache_url, $mtime);
            d($cache_url);
        }

        return $tags;
    }


    public static function is_variable_tag(App $object, $flags, $options, $content=''): bool
    {
        $explode = explode('$', $content, 2);
        $before = str_replace(
            [
                '!',
                '&',
                '(',
                ')',
                'integer',
                'int',
                'float',
                'double',
                'string',
                'array',
                'object',
                'bool',
                'clone',
                "\n",
                "\t",
                ' '
            ],
            '',
            $explode[0]
        );
        if($before === ''){
            return true;
        }
        return false;
    }

    public static function variable_name(App $object, $flags, $options, $content=''): bool | string
    {
        $explode = explode('$', $content, 2);
        $before = str_replace(
            [
                '!',
                '&',
                '(',
                ')',
                'integer',
                'int',
                'float',
                'double',
                'string',
                'array',
                'object',
                'bool',
                'clone',
                "\n",
                "\t",
                ' '
            ],
            '',
            $explode[0]
        );
        if(
            $before === '' &&
            array_key_exists(1, $explode)
        ){
            $temp = explode('::', $explode[1], 2);
            if(array_key_exists(1, $temp)){
                return '$' . $temp[0];
            }
            $temp = explode('->', $explode[1], 2);
            return '$' . $temp[0];
        }
        return false;
    }

    public static function cast_get(App $object, $flags, $options, $content=''): bool | string
    {

        $explode = explode(')', $content, 2);
        $explode_explode = explode('(', $explode[0], 2);
        $cast = trim($explode_explode[1] ?? '');
        switch($cast){
            case 'integer':
            case 'int':
                return 'int';
            case 'bool':
            case 'boolean':
                return 'bool';
            case 'float':
            case 'double':
            case 'string':
            case 'array':
            case 'object':
            case 'clone':
                return $cast;
        }
        return false;
    }


    /**
     * @throws Exception
     */
    public static function abstract_syntax_tree(App $object, $flags, $options, $tags=[]): array
    {
        if(!is_array($tags)){
            return $tags;
        }
        $cache = $object->get(App::CACHE);
        foreach($tags as $line => $tag){
            foreach($tag as $nr => $record){
                if(
                    array_key_exists('tag', $record)
                ){
                    $content = trim(mb_substr($record['tag'], 2, -2));
                    if(empty($content)){
                        unset($tags[$line][$nr]);
                    }
                    $hash = hash('sha256', 'tag.' . $content);
                    $is_variable_tag = Token::is_variable_tag($object, $flags, $options, $content);
                    if($is_variable_tag === true){
                        if($cache->has($hash)){
                            $variable = $cache->get($hash);
                        } else {
                            //we have a variable assign or define
                            $length = mb_strlen($content);
                            $data = mb_str_split($content, 1);
                            $operator = false;
                            $variable = [];
                            $variable_name = '';
                            $modifier_name = false;
                            $after = '';
                            $modifier = '';
                            $modifier_array = [];
                            $modifier_list = [];
                            $modifier_string = '';
                            $argument = '';
                            $argument_array = [];
                            $argument_list = [];
                            $method = '';
                            $method_array = [];
                            $is_after = false;
                            $is_modifier = false;
                            $is_method = false;
                            $is_single_quoted = false;
                            $is_double_quoted = false;
                            $after_array = [];
                            $set_depth = 0;
                            $array_depth = 0;
                            $curly_depth = 0;
                            $curly_depth_variable = false;
                            for($i=0; $i < $length; $i++){
                                $char = $data[$i];
                                if(array_key_exists($i - 1, $data)){
                                    $previous = $data[$i - 1];
                                    if(
                                        is_array($data[$i - 1]) &&
                                        array_key_exists('execute', $data[$i - 1])
                                    ){
                                        $previous = $data[$i - 1]['execute'];
                                    }
                                    elseif(
                                        is_array($data[$i - 1]) &&
                                        array_key_exists('value', $data[$i - 1])
                                    ){
                                        $previous = $data[$i - 1]['value'];
                                    }
                                } else {
                                    $previous = null;
                                }
                                if(array_key_exists($i + 1, $data)){
                                    $next = $data[$i + 1];
                                    if(
                                        is_array($data[$i + 1]) &&
                                        array_key_exists('execute', $data[$i + 1])){
                                        $next = $data[$i - 1]['execute'];
                                    }
                                    elseif(
                                        is_array($data[$i + 1]) &&
                                        array_key_exists('value', $data[$i + 1])){
                                        $next = $data[$i - 1]['value'];
                                    }
                                } else {
                                    $next = null;
                                }
                                if(
                                    $char === '\'' &&
                                    $is_single_quoted === false &&
                                    $previous !== '\\'
                                ){
                                    $is_single_quoted = true;
                                }
                                elseif(
                                    $char === '\'' &&
                                    $is_single_quoted === true &&
                                    $previous !== '\\'

                                ){
                                    $is_single_quoted = false;
                                }
                                elseif(
                                    $char === '"' &&
                                    $is_double_quoted === false &&
                                    $previous !== '\\'
                                ){
                                    $is_double_quoted = true;
                                }
                                elseif(
                                    $char === '"' &&
                                    $is_double_quoted === true &&
                                    $previous !== '\\'
                                ){
                                    $is_double_quoted = false;
                                }
                                elseif(
                                    $char === '(' &&
                                    $is_single_quoted === false
                                ){
                                    $set_depth++;
                                }
                                elseif(
                                    $char === ')' &&
                                    $is_single_quoted === false
                                ){
                                    $set_depth--;
                                }
                                elseif(
                                    $char === '[' &&
                                    $is_single_quoted === false
                                ){
                                    $array_depth++;
                                }
                                elseif(
                                    $char === ']' &&
                                    $is_single_quoted === false
                                ){
                                    $array_depth--;
                                }
                                elseif(
                                    $char === '{' &&
                                    $is_single_quoted === false
                                ){
                                    $curly_depth++;
                                }
                                elseif(
                                    $char === '}' &&
                                    $is_single_quoted === false
                                ){
                                    $curly_depth--;
                                }
                                if(
                                    $variable_name !== '' &&
                                    $char === '|' &&
                                    $next !== '|' &&
                                    $previous !== '|' &&
                                    $set_depth === 0 &&
                                    $array_depth === 0 &&
                                    $curly_depth === $curly_depth_variable &&
                                    $is_modifier === false &&
                                    $is_single_quoted === false &&
                                    $is_double_quoted === false
                                ){
                                    $is_after = true;
                                    $after .= $char;
                                    $after_array[] = $char;
//                                    $is_modifier = true;
                                    continue;
                                }
                                /*
                                elseif(
                                    !in_array(
                                        $modifier_name, [
                                            false,
                                            ''
                                        ],
                                        true
                                    )
                                ){
                                    if(
                                        in_array(
                                            $char,
                                            [
                                                " ",
                                                "\t",
                                                "\n",
                                                "\r"
                                            ],
                                            true
                                        ) &&
                                        $is_single_quoted === false &&
                                        $is_double_quoted === false
                                    ){
                                        $modifier_string .= $char;
                                        //nothing
                                    } else {
                                        if(
                                            $char === ':' &&
                                            $set_depth === 0 &&
                                            $is_single_quoted === false &&
                                            $is_double_quoted === false &&
                                            $curly_depth === $curly_depth_variable
                                        ){
                                            $modifier_string .= $char;
                                            d($argument);
                                            $argument_list[] = Token::value(
                                                $object,
                                                $flags,
                                                $options,
                                                [
                                                    'string' => $argument,
                                                    'array' => $argument_array
                                                ]
                                            );
                                            $argument = '';
                                            $argument_array = [];
                                        }
                                        elseif(
                                            $char === '|' &&
                                            $next !== '|' &&
                                            $previous !== '|' &&
                                            $set_depth === 0 &&
                                            $curly_depth === $curly_depth_variable &&
                                            $is_single_quoted === false &&
                                            $is_double_quoted === false
                                        ){
                                            $modifier_string .= $char;
                                            $argument_list[] = Token::value(
                                                $object,
                                                $flags,
                                                $options,
                                                [
                                                    'string' => $argument,
                                                    'array' => $argument_array
                                                ]
                                            );
                                            $argument = '';
                                            $argument_array = [];
                                            $modifier_list[] = [
                                                'string' => $modifier_string,
                                                'name' => $modifier_name,
                                                'argument' => $argument_list
                                            ];
                                            $modifier_name = false;
                                            $argument_list = [];
                                        } else {
                                            if(
                                                $char === ',' &&
                                                $is_single_quoted === false &&
                                                $is_double_quoted === false &&
                                                $curly_depth === $curly_depth_variable
                                            ){
                                                break;
                                            }
                                            $modifier_string .= $char;
                                            $argument .= $char;
                                            $argument_array[] = $char;
                                        }
                                    }
                                    continue;
                                }
                                elseif($is_modifier){
                                    if(
                                        $char === ':' &&
                                        $is_single_quoted === false &&
                                        $is_double_quoted === false &&
                                        $curly_depth === $curly_depth_variable
                                    ){
                                        $modifier_string .= $char;
                                        if($modifier !== ''){
                                            if($modifier_name === false){
                                                $modifier_name = $modifier;
                                                $modifier_string = $modifier . $char;
                                                $modifier = '';
                                                $modifier_array = [];
                                            }
                                        }
                                    }
                                    elseif(
                                        in_array(
                                            $char,
                                            [
                                                " ",
                                                "\t",
                                                "\n",
                                                "\r"
                                            ],
                                            true
                                        ) &&
                                        $is_single_quoted === false &&
                                        $is_double_quoted === false
                                    ){
                                        $modifier_string .= $char;
                                        //nothing
                                    } else {
                                        $modifier .= $char;
                                        $modifier_array[] = $char;
                                        $modifier_string .= $char;
                                    }
                                    continue;
                                }
                                */
                                elseif(
                                    !$operator &&
                                    (
                                        in_array(
                                            $char,
                                            [
                                                '=',
                                                '.',
                                                '+',
                                                '-',
                                                '*',
    //                                        '/', //++ -- ** // (// is always =1)
                                            ],
                                            true
                                        ) ||
                                        (
                                            $char === ':' &&
                                            $next === ':'
                                        )
                                    )
                                     &&
                                    $is_single_quoted === false &&
                                    $is_double_quoted === false
                                ){
                                    $operator = $char;
                                    continue;
                                }
                                if($operator && $is_after === false){
                                    if($operator === '.' && $char === '='){
                                        $operator .= $char;
                                        $is_after = true;
                                    }
                                    elseif($operator === '.'){
                                        //fix false positives
                                        $variable_name .= $operator . $char;
                                        $operator = false;
                                    }
                                    elseif(
                                        (
                                            $char === ' ' ||
                                            $char === "\t" ||
                                            $char === "\n" ||
                                            $char === "\r"
                                        ) &&
                                        $is_single_quoted === false &&
                                        $is_double_quoted === false &&
                                        $after === ''
                                    ) {
                                        continue;
                                    }
                                    elseif(
                                        (
                                            $operator === '+' &&
                                            $char === '+'
                                        ) ||
                                        (
                                            $operator === '-' &&
                                            $char === '-'
                                        ) ||
                                        (
                                            $operator === '*' &&
                                            $char === '*'
                                        )
                                    ){
                                        $operator .= $char;
                                        continue;
                                    }
                                    elseif(
                                        (
                                            $operator === ':' &&
                                            $char === ':'
                                        ) ||
                                        (
                                            $operator === '-' &&
                                            $char === '>'
                                        )
                                    ){
                                        $operator .= $char;
                                        $is_method = true;
                                        continue;
                                    }
                                    elseif(
                                        (
                                            $operator === '+' &&
                                            $char === '='
                                        ) ||
                                        (
                                            $operator === '-' &&
                                            $char === '='
                                        ) ||
                                        (
                                            $operator === '*' &&
                                            $char === '='
                                        )
                                    ){
                                        $operator .= $char;
                                        continue;
                                    }
                                    else {
                                        $is_after = true;
                                        if($is_method){
                                            $method .= $char;
                                            $method_array[] = $char;
                                        } else {
                                            $after .= $char;
                                            $after_array[] = $char;
                                        }
                                    }
                                }
                                elseif($is_after) {
                                    if(
                                        (
                                            $char === ' ' ||
                                            $char === "\t" ||
                                            $char === "\n" ||
                                            $char === "\r"
                                        ) &&
                                        $is_single_quoted === false &&
                                        $is_double_quoted === false &&
                                        $after === '' &&
                                        $method === ''
                                    ) {
                                        continue;
                                    }
                                    if($is_method){
                                        $method .= $char;
                                        $method_array[] = $char;
                                        if(
                                            $previous !== '\\' &&
                                            $char === ')' &&
                                            $set_depth === 0
                                        ){
                                            $is_method = false;
                                        }
                                    } else {
                                        $after .= $char;
                                        $after_array[] = $char;
                                    }
                                }
                                elseif(
                                    (
                                        $char !== ' ' &&
                                        $char !== "\t" &&
                                        $char !== "\r" &&
                                        $char !== "\n" &&
                                        $char !== '\'' &&
                                        $char !== '"'
                                    ) &&
                                    $is_single_quoted === false &&
                                    $is_double_quoted === false
                                ){
                                    $variable_name .= $char;
                                    if($curly_depth_variable === false){
                                        $curly_depth_variable = $curly_depth;
                                    }
                                }
                            }
                            /*
                            if($argument !== ''){
                                $argument_hash = hash('sha256', 'argument.' . $argument);
                                if($cache->has($argument_hash)){
                                    $argument_value = $cache->get($argument_hash);
                                } else {
                                    $argument_value = Token::value(
                                        $object,
                                        $flags,
                                        $options,
                                        [
                                            'string' => $argument,
                                            'array' => $argument_array
                                        ]
                                    );

                                    $cache->set($argument_hash, $argument_value);
                                }
                                $argument_list[] = $argument_value;
                                $argument = '';
                                $argument_array = [];
                            }
                            if($modifier_name){
                                $modifier_list[] = [
                                    'string' => $modifier_string,
                                    'name' => $modifier_name,
                                    'argument' => $argument_list
                                ];
                                $modifier_name = false;
                                $modifier_string = '';
                                $argument_list = [];
                            }
                            */
                            if($after === ''){
                                /*
                                if(array_key_exists(0, $modifier_list)){
                                    $variable_target = Token::variable_name($object, $flags, $options, $variable_name);
                                    $variable_explode = explode($variable_target, $variable_name, 2);
                                    $cast = Token::cast_get($object, $flags, $options, $variable_explode[0]);
                                    $is_not_count = mb_substr_count($variable_explode[0], '!');
                                    $is_not = null;
                                    if(
                                        in_array(
                                            $is_not_count,
                                            [
                                                2,
                                                4
                                            ],
                                            true
                                        )
                                    ){
                                        $is_not = true;
                                    }
                                    elseif(
                                        in_array(
                                            $is_not_count,
                                            [
                                                1,
                                                3,
                                            ],
                                            true
                                        )
                                    ){
                                        $is_not = false;
                                    }
                                    $variable = [
                                        'is_define' => true,
                                        'is_not' => $is_not,
                                        'name' => mb_substr($variable_target, 1),
                                        'modifier' => $modifier_list,
                                        'cast' => $cast
                                    ];
                                    d($variable_name);
                                    breakpoint($variable);
                                }
                                */
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
                                    $variable = [
                                        'is_assign' => true,
                                        'operator' => $operator,
                                        'name' => mb_substr($variable_name, 1)
                                    ];
                                } else {
                                    $variable_target = Token::variable_name($object, $flags, $options, $variable_name);
                                    $variable_explode = explode($variable_target, $variable_name, 2);
                                    $cast = Token::cast_get($object, $flags, $options, $variable_explode[0]);
                                    $is_not_count = mb_substr_count($variable_explode[0], '!');
                                    $is_not = null;
                                    if(
                                        in_array(
                                            $is_not_count,
                                            [
                                                2,
                                                4
                                            ],
                                            true
                                        )
                                    ){
                                        $is_not = true;
                                    }
                                    elseif(
                                        in_array(
                                            $is_not_count,
                                            [
                                                1,
                                                3,
                                            ],
                                            true
                                        )
                                    ){
                                        $is_not = false;
                                    }
                                    if($method){
                                        $method_value = Token::value(
                                            $object,
                                            $flags,
                                            $options,
                                            [
                                                'string' => $method,
                                                'array' => $method_array,
                                            ],
                                            $record
                                        );
                                        $variable = [
                                            'is_define' => true,
                                            'is_not' => $is_not,
                                            'operator' => $operator,
                                            'name' => mb_substr($variable_target, 1),
                                            'method' => $method_value['array'][0]['method'],
                                            'modifier' => [],
                                            'cast' => $cast
                                        ];
                                    } else {
                                        $variable = [
                                            'is_define' => true,
                                            'is_not' => $is_not,
                                            'name' => mb_substr($variable_target, 1),
                                            'cast' => $cast
                                        ];
                                    }
                                }
                            } else {
                                if($operator){
                                    $list = Token::value(
                                        $object,
                                        $flags,
                                        $options,
                                        [
                                            'string' => $after,
                                            'array' => $after_array,
                                        ],
                                        $record
                                    );
                                    $variable_target = Token::variable_name($object, $flags, $options, $variable_name);
                                    $variable_explode = explode($variable_target, $variable_name, 2);
                                    $cast = Token::cast_get($object, $flags, $options, $variable_explode[0]);
                                    $is_not_count = mb_substr_count($variable_explode[0], '!');
                                    $is_not = null;
                                    if(
                                        in_array(
                                            $is_not_count,
                                            [
                                                2,
                                                4
                                            ],
                                            true
                                        )
                                    ){
                                        $is_not = true;
                                    }
                                    elseif(
                                        in_array(
                                            $is_not_count,
                                            [
                                                1,
                                                3,
                                            ],
                                            true
                                        )
                                    ){
                                        $is_not = false;
                                    }
                                    if(
                                        in_array(
                                            $operator,
                                             [
                                                 '->',
                                                 '::'
                                             ],
                                            true
                                        )
                                    ){
                                        $method_value = Token::value(
                                            $object,
                                            $flags,
                                            $options,
                                            [
                                                'string' => $method,
                                                'array' => $method_array,
                                            ],
                                            $record
                                        );
                                        $list = Token::value(
                                            $object,
                                            $flags,
                                            $options,
                                            [
                                                'string' => $after,
                                                'array' => $after_array,
                                            ],
                                            $record
                                        );
                                        $is_argument = false;
                                        $argument_nr = -1;
                                        $argument = [];
                                        $argument_array = [];
                                        $modifier_name = '';
                                        $modifier_string = '';
                                        $modifier_array = [];
                                        foreach($list['array'] as $list_nr => $list_value){
                                            if(
                                                is_array($list_value) &&
                                                array_key_exists('value', $list_value) &&
                                                $list_value['value'] === '|'
                                            ){
                                                $argument_value = [];
                                                foreach($argument as $argument_nr => $argument_value_value){
                                                    $argument_value[] = [
                                                        'string' => $argument[$argument_nr],
                                                        'array' => $argument_array[$argument_nr]
                                                    ];
                                                }
                                                if($modifier_name !== ''){
                                                    $modifier_array[] = [
                                                        'string' => $modifier_string,
                                                        'name' => $modifier_name,
                                                        'argument' => $argument_value
                                                    ];
                                                }
                                                //new modifier
                                                $modifier_name = '';
                                                $modifier_string = '';
                                                $argument_nr = -1;
                                                $is_argument = false;
                                            }
                                            elseif(
                                                is_array($list_value) &&
                                                array_key_exists('value', $list_value) &&
                                                $list_value['value'] === ':'
                                            ){
                                                $modifier_string .= Token::item($list, $list_nr);
                                                $is_argument = true;
                                                $argument_nr++;
                                                $argument[$argument_nr] = '';
                                                $argument_array[$argument_nr] = [];
                                            }
                                            elseif($is_argument === false){
                                                $modifier_name .= Token::item($list, $list_nr);
                                                $modifier_string .= Token::item($list, $list_nr);
                                            }
                                            elseif($is_argument === true){
                                                $modifier_string .= Token::item($list, $list_nr);
                                                $argument[$argument_nr] .= Token::item($list, $list_nr);
                                                $argument_array[$argument_nr][] = $list_value;
                                            }
                                        }
                                        if($modifier_name !== ''){
                                            $argument_value = [];
                                            foreach($argument as $argument_nr => $argument_value_value){
                                                $argument_value[] = [
                                                    'string' => $argument[$argument_nr],
                                                    'array' => $argument_array[$argument_nr]
                                                ];
                                            }
                                            $modifier_array[] = [
                                                'string' => $modifier_string,
                                                'name' => $modifier_name,
                                                'argument' => $argument_value
                                            ];
                                        }
                                        $variable = [
                                            'is_define' => true,
                                            'is_not' => $is_not,
                                            'operator' => $operator,
                                            'name' => mb_substr($variable_target, 1),
                                            'method' => $method_value['array'][0]['method'],
                                            'modifier' => $modifier_array,
                                            'cast' => $cast
                                        ];
                                    } else {
                                        $variable = [
                                            'is_assign' => true,
                                            'is_not' => $is_not,
                                            'operator' => $operator,
                                            'name' => mb_substr($variable_target, 1),
                                            'value' => $list,
                                            'cast' => $cast
                                        ];
                                    }
//                                    $cache->set($after_hash, $list);
                                } else {
                                    $after = $variable_name . $after;
                                    $variable_target = Token::variable_name($object, $flags, $options, $variable_name);
                                    $variable_explode = explode($variable_target, $variable_name, 2);
                                    $cast = Token::cast_get($object, $flags, $options, $variable_explode[0]);
                                    $is_not_count = mb_substr_count($variable_explode[0], '!');
                                    $is_not = null;
                                    if(
                                        in_array(
                                            $is_not_count,
                                            [
                                                2,
                                                4
                                            ],
                                            true
                                        )
                                    ){
                                        $is_not = true;
                                    }
                                    elseif(
                                        in_array(
                                            $is_not_count,
                                            [
                                                1,
                                                3,
                                            ],
                                            true
                                        )
                                    ){
                                        $is_not = false;
                                    }
                                    array_unshift($after_array, [
                                        'type'=> 'variable',
                                        'tag' => $variable_name,
                                        'name' => mb_substr($variable_target, 1),
                                        'is_reference' => false,
                                        'is_not' => $is_not,
                                        'cast' => $cast
                                    ]);
                                    $list = Token::value(
                                        $object,
                                        $flags,
                                        $options,
                                        [
                                            'string' => $after,
                                            'array' => $after_array,
                                        ],
                                        $record
                                    );
                                    if(
                                        array_key_exists(0, $list['array']) &&
                                        is_array($list['array'][0]) &&
                                        array_key_exists('type', $list['array'][0]) &&
                                        $list['array'][0]['type'] === 'variable'
                                    ){
                                        $variable = $list['array'][0];
                                        $variable['is_define'] = true;
                                    }
                                }
                            }
                            $variable_name = '';
                            $curly_depth_variable = false;
//                            $cache->set($hash, $variable);
                        }
                        $tags[$line][$nr]['variable'] = $variable;
                    } else {
                        $method_hash = hash('sha256', 'method.' . $record['tag']);
                        if($cache->has($method_hash)){
                            $list = $cache->get($method_hash);
                        } else {
                            $tag_array = mb_str_split($record['tag'], 1);
                            $list = Token::value(
                                $object,
                                $flags,
                                $options,
                                [
                                    'string' => $record['tag'],
                                    'array' => $tag_array
                                ],
                                $tag
                            );
                        }
                        if(
                            array_key_exists(0, $list['array']) &&
                            is_array($list['array'][0]) &&
                            array_key_exists('type', $list['array'][0]) &&
                            $list['array'][0]['type'] === 'method' &&
                            array_key_exists('method', $list['array'][0])
                        ){
                            $tags[$line][$nr]['method'] = $list['array'][0]['method'];
                        } else {
                            $is_close = false;
                            $name = '';
                            if(
                                array_key_exists(0, $list['array']) &&
                                is_array($list['array'][0]) &&
                                array_key_exists('type', $list['array'][0]) &&
                                $list['array'][0]['type'] === 'symbol' &&
                                $list['array'][0]['value'] === '/'
                            ){
                                $is_close = true;
                                $temp['array'] = $list['array'];
                                array_shift($temp['array']);
                                foreach($temp['array'] as $temp_nr => $char){
                                    $current = Token::item($temp, $temp_nr);
                                    $name .= $current;
                                }
                            } else {
                                foreach($list['array'] as $temp_nr => $char){
                                    $current = Token::item($list, $temp_nr);
                                    $name .= $current;
                                }
                            }
                            if($name === ''){
                                $name = null;
                            }
                            $tags[$line][$nr]['marker'] = [
                                'value' => $list,
                                'is_close' => $is_close,
                                'name' => $name
                            ];
                        }
                    }
                }
            }
        }
        return $tags;
    }

    public static function value(App $object, $flags, $options, $input=[], $tag=[]): mixed
    {
        if(!is_array($input)){
            return $input;
        }
        if(!array_key_exists('array', $input)){
            return $input;
        }
        $value = $input['string'] ?? null;
        switch($value){
            case '[]':
                $input['array'] = [[
                    'type' => 'array',
                    'string' => $value,
                    'array' => [
                        [
                            'type' => 'symbol',
                            'value' => '[',
                        ],
                        [
                            'type' => 'symbol',
                            'value' => ']',
                        ]
                    ]
                ]];
                return $input;
            case 'true':
                $input['array'] = [[
                    'value' => $value,
                    'execute' => true,
                    'is_boolean' => true
                ]];
                return $input;
            case 'false':
                $input['array'] = [[
                    'value' => $value,
                    'execute' => false,
                    'is_boolean' => true
                ]];
                return $input;
            case 'null':
                $input['array'] = [[
                    'value' => $value,
                    'execute' => null,
                    'is_null' => true
                ]];
                return $input;
            default:
                $trim_value = trim($value);
                if(
                    $trim_value === '' &&
                    $trim_value !== $value
                ){
                    $input['array'] = [[
                        'type' => 'whitespace',
                        'value' => $value,
                    ]];
                    return $input;
                }
                elseif(
                    mb_substr($value, 0, 1) === '\'' &&
                    mb_substr($value, -1) === '\''
                ){
                    $input['array'] = [[
                        'value' => $value,
                        'execute' => $value,
                        'type' => 'string',
                        'is_single_quoted' => true
                    ]];
                    return $input;
                }
                return Token::value_split($object, $flags, $options, $input, $tag);
        }
    }

    public static function cleanup(App $object, $flags, $options, $input=[]): array
    {
        $is_single_quote = false;
        $is_single_comment = false;
        $is_double_quote = false;
        $is_double_quote_backslash = false;
        $is_parse = false;
        $whitespace_nr = false;
        $curly_depth = 0;
        foreach($input['array'] as $nr => $char){
            if(!is_numeric($nr)){
                // ',' in modifier causes this
                continue;
            }
            $previous = $input['array'][$nr - 1] ?? null;
            if(
                is_array($previous) &&
                array_key_exists('execute',  $previous)
            ){
                $previous = $previous['execute'];
            }
            elseif(
                is_array($previous) &&
                array_key_exists('value',  $previous)
            ){
                $previous = $previous['value'];
            }
            if(
                (
                    (
                        is_array($char) &&
                        array_key_exists('value', $char) &&
                        $char['value'] === '\''
                    ) ||
                    $char == '\''
                ) &&
                $is_single_quote === false &&
                $is_double_quote === false &&
                $previous !== '\\'
            ){
                $is_single_quote = true;
            }
            elseif(
                (
                    (
                        is_array($char) &&
                        array_key_exists('value', $char) &&
                        $char['value'] === '\''
                    ) ||
                    $char == '\''
                ) &&
                $is_single_quote === true &&
                $is_double_quote === false &&
                $previous !== '\\'
            ){
                $is_single_quote = false;
            }
            elseif(
                (
                    (
                        is_array($char) &&
                        array_key_exists('value', $char) &&
                        $char['value'] === '"'
                    ) ||
                    $char == '"'
                ) &&
                $is_single_quote === false &&
                $is_double_quote === false &&
                $previous !== '\\'
            ){
                $is_double_quote = true;
            }
            elseif(
                (
                    (
                        is_array($char) &&
                        array_key_exists('value', $char) &&
                        $char['value'] === '"'
                    ) ||
                    $char == '"'
                ) &&
                $is_single_quote === false &&
                $is_double_quote === true &&
                $previous !== '\\'
            ){
                $is_double_quote = false;
            }
            elseif(
                (
                    (
                        is_array($char) &&
                        array_key_exists('value', $char) &&
                        $char['value'] === '"'
                    ) ||
                    $char == '"'
                ) &&
                $is_single_quote === false &&
                $is_double_quote_backslash === false &&
                $previous === '\\'
            ){
                $is_double_quote_backslash = true;
            }
            elseif(
                (
                    (
                        is_array($char) &&
                        array_key_exists('value', $char) &&
                        $char['value'] === '"'
                    ) ||
                    $char == '"'
                ) &&
                $is_single_quote === false &&
                $is_double_quote_backslash === true &&
                $previous === '\\'
            ){
                $is_double_quote_backslash = false;
            }
            /*
            elseif(
                $is_single_quote === false &&
                $is_double_quote === false &&
                $is_double_quote_backslash === false &&
                is_array($char) &&
                array_key_exists('value', $char) &&
                $char['value'] === '//'
            ){
                $is_single_comment = true;
            }
            */
            elseif(
                is_array($char) &&
                array_key_exists('value', $char) &&
                $char['value'] === '{{' &&
                $is_single_comment === false
            ){
                $is_parse = true;
                $curly_depth++;
            }
            elseif(
                $is_parse === true &&
                is_array($char) &&
                array_key_exists('value', $char) &&
                $char['value'] === '}}' &&
                $is_single_comment === false
            ){
                $curly_depth--;
                if($curly_depth === 0){
                    $is_parse = false;
                    unset($input['array'][$nr]);
                }
            }
            elseif(
                (
                    in_array(
                        $char,
                        [
                            null,
                            ' ',
                            "\t",
                            "\n",
                            "\r"
                        ],
                        true
                    ) ||
                    is_array($char) &&
                    array_key_exists('type', $char) &&
                    $char['type'] === 'whitespace'
                ) &&
                (
                    (
                        $is_single_quote === false &&
                        $is_double_quote === false
                    ) ||
                    (
                        $is_single_quote === false &&
                        $is_double_quote === true &&
                        $is_parse === true
                    )
                )
            ){
                if(
                    $is_single_comment &&
                    is_array($char) &&
                    array_key_exists('value', $char) &&
                    $char['value'] === "\n"
                ){
                    $is_single_comment = false;
                }
                unset($input['array'][$nr]);
            }
            elseif($char === null){
                unset($input['array'][$nr]);
            }
            if(
                is_array($char) &&
                array_key_exists('type', $char) &&
                $char['type'] === 'whitespace'
            ){
                if($whitespace_nr === false){
                    $whitespace_nr = $nr;
                }
                elseif(array_key_exists($whitespace_nr, $input['array'])) {
                    $input['array'][$whitespace_nr]['value'] .= $char['value'];
                    unset($input['array'][$nr]);
                }
            } else {
                $whitespace_nr = false;
            }
            if(
                is_array($char) &&
                array_key_exists('value', $char) &&
                $is_single_quote === false &&
                $is_double_quote === false &&
                $is_double_quote_backslash === false &&
                in_array(
                    $char['value'],
                    [
                        '{{',
                        '}}'
                    ],
                    true
                )
            ){
                unset($input['array'][$nr]);
            }
            /*
            if($is_single_comment){
                unset($input['array'][$nr]);
            }
            */
        }
        //re-index from 0
        $input['array'] = array_values($input['array']);
        return $input;
    }

    public static function value_split(App $object, $flags, $options, $input=[], $tag=[]): array
    {
        if(!is_array($input)){
            return $input;
        }
        if(array_key_exists('array', $input) === false){
            return $input;
        }
        if(array_key_exists('string', $input) === false){
            return $input;
        }
        $cache = $object->get(App::CACHE);
        $hash = hash('sha256', $input['string']);
        if($cache->has($hash)){
            $input = $cache->get($hash);
        } else {
//            d($input);
            $input = Symbol::define($object, $flags, $options, $input);
//            $input = Token::remove_comment($object, $flags, $options, $input);
//            breakpoint($input);
            $input = Cast::define($object, $flags, $options, $input);
            $input = Method::define($object, $flags, $options, $input, $tag);
            $input = Variable::define($object, $flags, $options, $input);
            $input = Variable::modifier($object, $flags, $options, $input, $tag);
            $input = Variable::assign($object, $flags, $options, $input);
            $input = Value::define($object, $flags, $options, $input);
            $input = Value::float($object, $flags, $options, $input);
            $input = Value::double_quoted_string($object, $flags, $options, $input, false);
            $input = Value::double_quoted_string($object, $flags, $options, $input, true);
            $input = Value::array($object, $flags, $options, $input);
//            $input = Method::block($object, $flags, $options, $input);
            $input = Token::cleanup($object, $flags, $options, $input);
            $cache->set($hash, $input);
        }
        return $input;
    }

    public static function item($input, $index=null){
        if (
            array_key_exists($index, $input['array']) &&
            is_array($input['array'][$index])
        ) {
            if (array_key_exists('execute', $input['array'][$index])) {
                $item = $input['array'][$index]['execute'] ?? null;
            }
            elseif (array_key_exists('tag', $input['array'][$index])) {
                $item = $input['array'][$index]['tag'] ?? null;
                if(
                    array_key_exists('modifier', $input['array'][$index]) &&
                    is_array($input['array'][$index]['modifier'])
                ){
                    foreach($input['array'][$index]['modifier'] as $modifier){
                        if(array_key_exists('string', $modifier)){
                            $item .= $modifier['string'];
                        } else {
                            d($input['array'][$index]);
                            trace();
                            die;
                        }

                    }
                }
            }
            elseif (array_key_exists('value', $input['array'][$index])) {
                $item = $input['array'][$index]['value'] ?? null;
            } else {
                $item = null;
            }
        } else {
            $item = $input['array'][$index] ?? null;
        }
        return $item;
    }

}