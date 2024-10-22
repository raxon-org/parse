<?php
namespace Package\Raxon\Parse\Service;

use Raxon\App;

use Raxon\Module\Core;
use Raxon\Module\File;

use Exception;
class Method
{
    public static function define(App $object, $flags, $options, $input=[]): array
    {
        if(!is_array($input)){
            return $input;
        }
        if(array_key_exists('array', $input) === false){
            return $input;
        }
        $has_name = false;
        $name = false;
        $is_method = false;
        $set_depth = 0;
        $array_depth = 0;
        $is_single_quote = false;
        $is_double_quote = false;
        $is_class_method = false;
        $argument = '';
        $argument_array = [];
        $argument_list = [];
        $argument_nr = 0;
        $separator = ',';
        $call_type = '';
        $is_for = false;
        $is_variable_method = false;
        foreach($input['array'] as $nr => $char){
            if(!is_numeric($nr)){
                // ',' in modifier causes this
                continue;
            }
            $previous = Token::item($input, $nr - 1);
            $next = Token::item($input, $nr + 1);
            if(
                is_array($char) &&
                array_key_exists('value', $char) &&
                $char['value'] === '(' &&
                $is_method === false
            ){
                $name = '';
                $is_method = $nr;
                for($i = $nr - 1; $i >= 0; $i--){
                    if($input['array'][$i] !== null){
                        if(is_array($input['array'][$i])){
                            if(
                                array_key_exists('value', $input['array'][$i]) &&
                                in_array(
                                    $input['array'][$i]['value'],
                                    [
                                        '.',
                                        '_',
                                        ':',
                                        '::',
                                        '->',
                                        '$'
                                    ],
                                    true
                                ) &&
                                $name !== ''
                            ){
                                if($input['array'][$i]['value'] === '::'){
                                    $is_class_method = true;
                                    $call_type = '::';
                                    $name .= $input['array'][$i]['value'];
                                }
                                elseif($input['array'][$i]['value'] === '->'){
                                    $is_class_method = true;
                                    $call_type = '->';
                                    $name .= '>-'; //we are going to reverse this
                                }
                                elseif($input['array'][$i]['value'] === '$'){
                                    $is_variable_method = true;
                                    $name .= $input['array'][$i]['value'];
                                } else {
                                    $name .= $input['array'][$i]['value'];
                                }

                            } else {
                                break;
                            }
                        } else {
                            if(
                                in_array(
                                    $input['array'][$i],
                                    [
                                        null,
                                        ' ',
                                        "\n",
                                        "\r",
                                        "\t"
                                    ],
                                    true
                                ) &&
                                $is_single_quote === false &&
                                $is_double_quote === false &&
                                $name !== ''
                            ){
                                break;
                            }
                            elseif(
                                in_array(
                                    $input['array'][$i],
                                    [
                                        null,
                                        ' ',
                                        "\n",
                                        "\r",
                                        "\t"
                                    ],
                                    true
                                )
                            ){
                                continue;
                            }
                            else {
                                $name .= $input['array'][$i];
                            }
                        }
                    }
                }
                if($name === ''){
                    $is_method = false;
                }
                if($name && $has_name === false){
                    if(mb_substr($name, 0, 1) === ':'){
                        //modifier with argument set
                        $name = '';
                        $is_method = false;
                    } else {
                        $name = strrev($name);
                        if($is_class_method){
                            $explode = explode($call_type, $name, 2);
                            if(array_key_exists(1, $explode)){
                                $class = $explode[0];
                                $name = $explode[1];
                            }
                        }
                        $has_name = true;
                    }
                }
            }
            if(
                $is_method !== false &&
                $name &&
                $has_name === true
            ){
                if($name === 'for'){
                    $old_separator = $separator;
                    $separator = ';';
                    $is_for = true;
                }
                if(
                    is_array($char) &&
                    array_key_exists('value', $char) &&
                    $char['value'] === '('
                ) {
                    $set_depth++;
                    if($set_depth !== 1){
                        $argument_array[] = $char;
                        $argument .= $char['value'];
                    }
                }
                elseif(
                    is_array($char) &&
                    array_key_exists('value', $char) &&
                    $char['value'] === ')'
                ){
                    $set_depth--;
                    if($set_depth !== 0){
                        $argument_array[] = $char;
                        $argument .= $char['value'];
                    } else{
                        if(array_key_exists(0, $argument_array)){
                            $argument_value = Cast::define(
                                $object,
                                $flags,
                                $options,
                                [
                                    'string' => $argument,
                                    'array' => $argument_array
                                ]
                            );
                            $argument_value = Token::value(
                                $object,
                                $flags,
                                $options,
                                $argument_value
                            );
                            $argument_list[$argument_nr] = $argument_value;
                            $argument_array = [];
                            $argument = '';
                            $argument_nr = 0;
                        } else {
                            if($name === 'for'){
                                $argument_array[] = [
                                    'type' => 'null',
                                    'value' => 'null',
                                    'execute' => null,
                                    'is_null' => true
                                ];
                                $argument = 'null';
                                $argument_list[$argument_nr] = [
                                    'string' => $argument,
                                    'array' => $argument_array
                                ];
                            }
                            $argument_array = [];
                            $argument = '';
                            $argument_nr = 0;
                        }
                        $input['array'][$is_method]['method'] = [
                            'name' => $name,
                            'argument' => $argument_list
                        ];
                        $input['array'][$is_method]['type'] = 'method';
                        $input['array'][$is_method]['tag'] = $input['string'] ?? '';
                        $input['array'][$is_method]['line'] = 'unknown';
                        $input['array'][$is_method]['length'] = 'unknown';
                        $input['array'][$is_method]['column'] = [
                            'start' => 0,
                            'end' => 0
                        ];
                        if($is_variable_method === true){
                            $call_type = '::';
                            $explode = explode($call_type, $name, 2);
                            if(array_key_exists(1, $explode)){
                                $input['array'][$is_method]['method']['name'] = $explode[1];
                            } else {
                                $call_type = '->';
                                $explode = explode($call_type, $name, 2);
                                if(array_key_exists(1, $explode)){
                                    $input['array'][$is_method]['method']['name'] = $explode[1];
                                }
                            }
                            $input['array'][$is_method]['type'] = 'variable_method';
                            $input['array'][$is_method]['method']['call_type'] = $call_type;
                            $input['array'][$is_method]['variable'] = [
                                'type' => 'variable',
                                'tag' => $explode[0],
                                'name' => mb_substr($explode[0], 1),
                                'is_reference' => false
                            ];
                            $input['array'][$is_method]['tag'] = $name .'(';
                            $argument_count = count($argument_list);
                            foreach($argument_list as $argument_nr => $argument){
                                $input['array'][$is_method]['tag'] .= $argument['string'];
                                if($argument_nr < $argument_count - 1){
                                    $input['array'][$is_method]['tag'] .= ', ';
                                }
                            }
                            $input['array'][$is_method]['tag'] .= ')';
                        }
                        elseif($is_class_method === true){
                            $input['array'][$is_method]['method']['is_class_method'] = true;
                            $input['array'][$is_method]['method']['class'] = $class ?? null;
                            $input['array'][$is_method]['method']['call_type'] = $call_type;
                        }
                        unset($input['array'][$is_method]['value']);
                        $argument_list = [];
                        $argument_array = [];
                        $argument = '';
                        $argument_nr = 0;
                        $method_name_reverse = '';
                        for($i = $is_method - 1; $i >= 0; $i--){
                            if(
                                !is_array($input['array'][$i]) &&
                                in_array(
                                    $input['array'][$i],
                                    [
                                        null,
                                        ' ',
                                        "\n",
                                        "\r",
                                        "\t",
                                    ],
                                    true
                                ) &&
                                $is_single_quote === false &&
                                $is_double_quote === false &&
                                $is_class_method === false &&
                                $method_name_reverse !== ''
                            ){
                                break;
                            }
                            elseif(
                                !is_array($input['array'][$i]) &&
                                in_array(
                                    $input['array'][$i],
                                    [
                                        ' ',
                                        "\n",
                                        "\r",
                                        "\t",
                                    ],
                                    true
                                ) &&
                                $is_single_quote === false &&
                                $is_double_quote === false &&
                                $is_class_method === true &&
                                $method_name_reverse !== ''
                            ){
                                break;
                            }
                            elseif(is_array($input['array'][$i])){
                                if(
                                    array_key_exists('value', $input['array'][$i]) &&
                                    in_array(
                                        $input['array'][$i]['value'],
                                        [
                                            '.',
                                            '_',
                                            ':',
                                            '::',
                                            '->',
                                            '$'
                                        ],
                                        true
                                    )
                                ){
                                    $method_name_reverse .= $input['array'][$i]['value'];
                                    $input['array'][$i] = null;
                                }
                                elseif(
                                    $input['array'][$i]['value'] === '|' &&
                                    $previous !== '|' &&
                                    $next !== '|' &&
                                    $is_single_quote === false &&
                                    $is_double_quote === false
                                ){
                                    break;
                                }
                            } else {
                                if(
                                    !in_array(
                                        $input['array'][$i],
                                        [
                                            ' ',
                                            "\n",
                                            "\r",
                                            "\t"
                                        ], true
                                    )
                                ){
                                    $method_name_reverse .= $input['array'][$i];
                                }
                                $input['array'][$i] = null;
                            }
                        }
                        for($i = $is_method + 1; $i <= $nr; $i++){
                            $input['array'][$i] = null;
                        }
                        // add modifier for methods
                        $is_method = false;
                        $is_variable_method = false;
                        $has_name = false;
                    }
                }
                elseif($set_depth > 0){
                    if(
                        is_array($char) &&
                        array_key_exists('value', $char) &&
                        $char['value'] === '\'' &&
                        $previous !== '\\' &&
                        $is_single_quote === false &&
                        $is_double_quote === false
                    ){
                        $is_single_quote = true;
                        $argument_array[] = $char;
                        $argument .= $char['value'];
                    }
                    elseif(
                        is_array($char) &&
                        array_key_exists('value', $char) &&
                        $char['value'] === '\'' &&
                        $previous !== '\\' &&
                        $is_single_quote === true &&
                        $is_double_quote === false
                    ){
                        $is_single_quote = false;
                        $argument_array[] = $char;
                        $argument .= $char['value'];
                    }
                    elseif(
                        is_array($char) &&
                        array_key_exists('value', $char) &&
                        $char['value'] === '"' &&
                        $previous !== '\\' &&
                        $is_single_quote === false &&
                        $is_double_quote === false
                    ){
                        $is_double_quote = true;
                        $argument_array[] = $char;
                        $argument .= $char['value'];
                    }
                    elseif(
                        is_array($char) &&
                        array_key_exists('value', $char) &&
                        $char['value'] === '"' &&
                        $previous !== '\\' &&
                        $is_single_quote === false &&
                        $is_double_quote === true
                    ){
                        $is_double_quote = false;
                        $argument_array[] = $char;
                        $argument .= $char['value'];
                    }
                    elseif(
                        is_array($char) &&
                        array_key_exists('value', $char) &&
                        $char['value'] === '[' &&
                        $is_single_quote === false &&
                        $is_double_quote === false
                    ){
                        $array_depth++;
                        $argument_array[] = $char;
                        $argument .= $char['value'];
                    }
                    elseif(
                        is_array($char) &&
                        array_key_exists('value', $char) &&
                        $char['value'] === ']' &&
                        $is_single_quote === false &&
                        $is_double_quote === false
                    ){
                        $array_depth--;
                        $argument_array[] = $char;
                        $argument .= $char['value'];
                    }
                    elseif(
                        is_array($char) &&
                        array_key_exists('value', $char) &&
                        $char['value'] === $separator &&
                        $set_depth === 1 &&
                        $array_depth === 0 &&
                        $is_single_quote === false &&
                        $is_double_quote === false
                    ){
                        if(array_key_exists(0, $argument_array)){
                            $argument_value = Cast::define(
                                $object,
                                $flags,
                                $options,
                                [
                                    'string' => $argument,
                                    'array' => $argument_array
                                ]
                            );
                            $argument_value = Token::value(
                                $object,
                                $flags,
                                $options,
                                $argument_value,
                            );
                            $argument_list[$argument_nr] = $argument_value;
                            $argument_array = [];
                            $argument = '';
                        } else {
                            if($name === 'for'){
                                $argument_array[] = [
                                    'type' => 'null',
                                    'value' => 'null',
                                    'execute' => null,
                                    'is_null' => true
                                ];
                                $argument = 'null';
                                $argument_list[$argument_nr] = [
                                    'string' => $argument,
                                    'array' => $argument_array
                                ];
                            }
                            $argument_array = [];
                            $argument = '';
                        }
                        $argument_nr++;
                    } else {
                        if(
                            is_string($char) &&
                            in_array(
                                $char,
                                [
                                    ' ',
                                    "\n",
                                    "\r",
                                    "\t"
                                ],
                                true
                            ) &&
                            $is_single_quote === false &&
                            $is_double_quote === false
                        ){
                            $argument .= $char;
                            $argument_array[] = $char;
                        } else {
                            $argument_array[] = $char;
                            if(is_array($char) && array_key_exists('value', $char)){
                                $argument .= $char['value'];
                            } else {
                                $argument .= $char;
                            }
                        }
                    }
                }
                if($name === 'for'){
                    $separator = $old_separator;
                }
            }
        }
        return $input;
    }
}