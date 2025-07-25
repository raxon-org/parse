<?php
namespace Raxon\Parse\Module;

use Raxon\App;

use Raxon\Exception\ObjectException;
use Raxon\Module\Core;
use Raxon\Module\File;

use Exception;
class Variable
{

    public static function assign(App $object, $flags, $options, $input=[]): array
    {
        if(!is_array($input)){
            return $input;
        }
        if(array_key_exists('array', $input) === false){
            return $input;
        }
        $variable_nr = false;
        $count = count($input['array']);
        $array_depth = 0;
        $set_depth = 0;
        foreach($input['array'] as $nr => $char) {
            if (!is_numeric($nr)) {
                // ',' in modifier causes this
                continue;
            }
            if(is_array($char)){
                if(
                    array_key_exists('type', $char) &&
                    $char['type'] === 'variable'
                ){
                    $variable_nr = $nr;
//                    d($variable_nr);
//                    d($char);
                }
                elseif(
                    array_key_exists('value', $char) &&
                    $char['value'] === '['
                ){
                    $array_depth++;
                }
                elseif(
                    array_key_exists('value', $char) &&
                    $char['value'] === ']'
                ){
                    $array_depth--;
                }
                elseif(
                    array_key_exists('value', $char) &&
                    $char['value'] === '('
                ){
                    $set_depth++;
                }
                elseif(
                    array_key_exists('value', $char) &&
                    $char['value'] === ')'
                ){
                    $set_depth--;
                }
                elseif(
                    array_key_exists('value', $char) &&
                    in_array(
                        $char['value'],
                        [
                            '=',
                            '.=',
                            '+=',
                            '-=',
                            '*=',
                            '%=',
                            '++',
                            '--',
                            '**'
                        ],
                        true
                    ) &&
                    $variable_nr !== false
                ){
                    $after = '';
                    $after_array = [];
                    $input['array'][$nr] = null;
                    for($i = $nr + 1; $i < $count; $i++){
                        if(
                            is_array($input['array'][$i]) &&
                            array_key_exists('value', $input['array'][$i]) &&
                            $input['array'][$i]['value'] === '['
                        ){
                            $array_depth++;
                        }
                        elseif(
                            is_array($input['array'][$i]) &&
                            array_key_exists('value', $input['array'][$i]) &&
                            $input['array'][$i]['value'] === ']'
                        ){
                            $array_depth--;
                        }
                        elseif(
                            is_array($input['array'][$i]) &&
                            array_key_exists('value', $input['array'][$i]) &&
                            $input['array'][$i]['value'] === '('
                        ){
                            $set_depth++;
                        }
                        elseif(
                            is_array($input['array'][$i]) &&
                            array_key_exists('value', $input['array'][$i]) &&
                            $input['array'][$i]['value'] === ')'
                        ){
                            $set_depth--;
                        }
                        elseif(
                            is_array($input['array'][$i]) &&
                            array_key_exists('value', $input['array'][$i]) &&
                            $input['array'][$i]['value'] === ',' &&
                            $array_depth === 0 &&
                            $set_depth === 0
                        ){
                            if($variable_nr !== false){
                                if($after === ''){
                                    if(
                                        in_array(
                                            $char['value'],
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
                                            'operator' => $char['value'],
                                            'name' => $input['array'][$variable_nr]['name']
                                        ];
                                        $input['array'][$variable_nr]['variable'] = $variable;
                                    }
                                } else {
                                    $list = Token::value(
                                        $object,
                                        $flags,
                                        $options,
                                        [
                                            'string' => $after,
                                            'array' => $after_array,
                                        ]
                                    );
                                    $variable = [
                                        'is_assign' => true,
                                        'operator' => $char['value'],
                                        'name' => $input['array'][$variable_nr]['name'],
                                        'value' => $list,
                                    ];
                                    $input['array'][$variable_nr]['variable'] = $variable;
                                }
                            }
                            $variable_nr = false;
                            break;
                        }
                        $current = Token::item($input, $i);
                        $after .= $current;
                        $after_array[] = $input['array'][$i];
                        $input['array'][$i] = null;
                    }
                    if($variable_nr !== false){
                        if($after === ''){
                            if(
                                in_array(
                                    $char['value'],
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
                                    'operator' => $char['value'],
                                    'name' => $input['array'][$variable_nr]['name']
                                ];
                                $input['array'][$variable_nr]['variable'] = $variable;
                            }
                        } else {
                            $list = Token::value(
                                $object,
                                $flags,
                                $options,
                                [
                                    'string' => $after,
                                    'array' => $after_array,
                                ]
                            );
                            $variable = [
                                'is_assign' => true,
                                'operator' => $char['value'],
                                'name' => $input['array'][$variable_nr]['name'],
                                'value' => $list,
                            ];
                            $input['array'][$variable_nr]['variable'] = $variable;
                            $variable_nr = false;
                        }
                    }
                }
                /* not this
                elseif($variable_nr !== false){
                    $input['array'][$nr] = null;
                }
                 else {
                    $input['array'][$nr] = null;
                }
                */
            }
        }
//        breakpoint($input);
        return $input;
    }

    public static function define(App $object, $flags, $options, $input=[]): array
    {
        if(!is_array($input)){
            return $input;
        }
        if(array_key_exists('array', $input) === false){
            return $input;
        }
//        trace();
//        d($input['array']);
        $count = count($input['array']);
        $is_variable = false;
        $has_name = false;
        $name = '';
//        breakpoint($input);
        $array_depth = 0;
        foreach($input['array'] as $nr => $char){
            if(!is_numeric($nr)){
                // ',' in modifier causes this
                continue;
            }
            $previous = Token::item($input, $nr - 1);
            $next = Token::item($input, $nr + 1);
            $current = Token::item($input, $nr);
            if($current === '$'){
                $is_variable = $nr;
                $is_array_notation = false;
                $name = '$';
                $array_notation = '';
                $array_notation_array = [];
                $list = [];
                for($i = $is_variable + 1; $i < $count; $i++){
                    $current = Token::item($input, $i);
                    if($current === '['){
                        $is_array_notation = true;
                        $array_depth++;
                    }                    
                    if($is_array_notation === true){
                        $array_notation .= $current;
                        $array_notation_array[] = $current;
                    } else {
                        if(
                            in_array(
                                $current,
                                [
                                    ' ',
                                    "\t",
                                    "\n",
                                    "\r"
                                ],
                                true
                            ) ||
                            (
                                is_array($input['array'][$i]) &&
                                array_key_exists('type', $input['array'][$i]) &&
                                $input['array'][$i]['type'] === 'symbol' &&
                                !in_array(
                                    $current,
                                    [
                                        '.',
                                        ':',
                                        '_',
                                        '#',
                                    ],
                                    true
                                )
                            )
                        ){
//                            d($current);
//                            d($name);
                            if($name !== '$'){
                                $has_name = true;
                                $is_reference = false;
                                $is_not = null; //neutral
                                if(
                                    in_array(
                                        $previous,
                                        [
                                            '!',
                                            '!!!',
                                        ],
                                        true
                                    )
                                ){
                                    $is_not = false;
                                    $input['array'][$is_variable - 1] = null;
                                }
                                elseif(
                                    in_array(
                                        $previous,
                                        [
                                            '!!',
                                            '!!!!'
                                        ],
                                        true
                                    )
                                ){
                                    $is_not = true;
                                    $input['array'][$is_variable - 1] = null;
                                }
                                elseif ($previous === '&') {
                                    $is_reference = true;
                                    $input['array'][$is_variable - 1] = null;
                                }
                                if($array_notation !== ''){
                                    $list = Token::value(
                                        $object,
                                        $flags,
                                        $options,
                                        [
                                            'string' => $array_notation,
                                            'array' => $array_notation_array,
                                        ],
                                    );
                                }
                                $input['array'][$is_variable] = [
                                    'type' => 'variable',
                                    'tag' => $name,
                                    'name' => mb_substr($name, 1),
                                    'is_reference' => $is_reference,
                                    'is_not' => $is_not,
                                    'array_notation' => $list
                                ];
                                $name = '';
                                $has_name = false;
                                for($j = $is_variable + 1; $j < $i; $j++){
                                    $input['array'][$j] = null;
                                }
                                $is_variable = false;
                                break;
                            }
                        }
                        elseif($has_name === false){
                            $name .= $current;
                        }
                        elseif($current === ']'){
                            $array_depth--;
                            if($array_depth === 0){
                                $is_array_notation = false;
                            }
                        }
                    }
                }
                if(
                    !in_array(
                        $name,
                        [
                            '',
                            '$'
                        ],
                        true
                    )
                ){
                    $is_reference = false;
                    $is_not = null; //neutral
                    if(
                        in_array(
                            $previous,
                            [
                                '!',
                                '!!!',
                            ],
                            true
                        )
                    ){
                        $is_not = false;
                        $input['array'][$is_variable - 1] = null;
                    }
                    elseif(
                        in_array(
                            $previous,
                            [
                                '!!',
                                '!!!!'
                            ],
                            true
                        )
                    ){
                        $is_not = true;
                        $input['array'][$is_variable - 1] = null;
                    }
                    elseif ($previous === '&') {
                        $is_reference = true;
                        $input['array'][$is_variable - 1] = null;
                    }
                    if($array_notation !== ''){
                        $list = Token::value(
                            $object,
                            $flags,
                            $options,
                            [
                                'string' => $array_notation,
                                'array' => $array_notation_array,
                            ],
                        );
                    }
                    $input['array'][$is_variable] = [
                        'type' => 'variable',
                        'tag' => $name,
                        'name' => mb_substr($name, 1),
                        'is_reference' => $is_reference,
                        'is_not' => $is_not,
                        'array_notation' => $list
                    ];
                    for($j = $is_variable + 1; $j < $i; $j++){
                        $input['array'][$j] = null;
                    }
                    break;
                }
            }
        }
        return $input;
    }

    public static function modifier(App $object, $flags, $options, $input=[], $tag=[]): array
    {
        if(!is_array($input)){
            return $input;
        }
        if(array_key_exists('array', $input) === false){
            return $input;
        }
        $count = count($input['array']);
        $set_depth = 0;
        $set_depth_modifier = false;
        $set_depth_argument = 0;
        $set_skip = 0;
        $outer_curly_depth = 0;
        $curly_depth = 0;
        $outer_set_depth = 0;
        $modifier_string = '';
        $modifier_name = '';
        $is_variable = false;
        $is_modifier = false;
        $is_argument = false;
        $is_single_quote = false;
        $is_double_quote = false;
        $is_double_quote_backslash = false;
        $is_array = false;
        $array_depth = 0;
        $array_depth_modifier = false;
        $argument_nr = -1;
        $argument = [];
        $argument_array = [];
        $nr = $count - 1;
        foreach($input['array'] as $nr => $char) {
            if(!is_numeric($nr)){
                // ',' in modifier causes this
                continue;
            }
            $previous = Token::item($input, $nr - 1);
            $previous_previous = Token::item($input, $nr - 2);
            $next = Token::item($input, $nr + 1);
            $current = Token::item($input, $nr);
            if($current === '('){
                $set_depth++;
                if(array_key_exists($argument_nr, $argument)){
                    $set_depth_argument++;
                }
            }
            elseif($current === ')'){
                $set_depth--;
                if(array_key_exists($argument_nr, $argument)){
                    $set_depth_argument--;
                }
                if($set_depth < 0){
//                    $input['array'][$nr] = null; //disabled @2024-09-29 (maybe return here)
                }
                if(
                    $is_modifier &&
                    (
                        $set_depth === $set_depth_modifier - 1 ||
                        $set_depth_modifier === false
                    ) &&
                    (
                        $array_depth === $array_depth_modifier - 1 ||
                        $array_depth_modifier === false
                    )
                ){
                    if(
                        $argument_nr >= 0 &&
                        $set_depth >= 0 &&
                        $set_depth_argument >= 0
                    ){
                        if(!array_key_exists($argument_nr, $argument)){
                            $argument_array[$argument_nr] = [];
                            $argument[$argument_nr] = '';
                        }
                        $argument[$argument_nr] .= $current;
                        $argument_array[$argument_nr][] = $char;
                        $modifier_string .= $current;
                    }
                    elseif($set_depth_argument < 0){
                        for($i = $nr - 1; $i >= 0; $i--){
                            $current = Token::item($input, $i);
                            if($current === '('){
                                $set_depth_argument++;
                            }
                            if($current === ')'){
                                $set_depth_argument--;
                            }
                            if($set_depth_argument === 0){
                                break;
                            }
                        }
                        if($set_depth_argument !== 0){
                            d($nr);
                            d($input);
                            ddd($set_depth_argument);
                        }
                    }
                    foreach($argument_array as $argument_nr => $array){
                        $argument_value = Cast::define(
                            $object,
                            $flags,
                            $options,
                            [
                                'string' => $argument[$argument_nr],
                                'array' => $array
                            ]
                        );
                        $argument_value = Token::value(
                            $object,
                            $flags,
                            $options,
                            $argument_value,
                            $tag
                        );
                        $argument_array[$argument_nr] = $argument_value;
                    }
//                    d($modifier_name);
//                    d($modifier_string);
//                    d($argument_array);
                    $input['array'][$is_variable]['modifier'][] = [
                        'string' => $modifier_string,
                        'name' => $modifier_name,
                        'argument' => $argument_array
                    ];
                    $index_set_depth = 0;
                    //check this
                    for($index = $is_variable + 1; $index < $nr; $index++){
                        $input['array'][$index] = null;
                    }
                    for($index = 0; $index < $nr; $index++){
                        $current = Token::item($input, $index);
                        if($current === '('){
                            $index_set_depth++;
                        }
                        elseif($current === ')'){
                            $index_set_depth--;
                        }
                    }
                    $current = Token::item($input, $nr);
                    if($current === '('){
                        $index_set_depth++;
                    }
                    elseif($current === ')'){
                        $index_set_depth--;
                    }
                    if($index_set_depth < 0){
                        $input['array'][$nr] = null;
                    }
                    $modifier_name = '';
                    $modifier_string = '';
                    $is_argument = false;
                    $is_variable = false;
                    $is_modifier = false;
                    $argument_array = [];
                    $argument = [];
                    $argument_nr = -1;
                    $set_depth_modifier = false;
                    $array_depth_modifier = false;
                }
            }
            elseif($current === '{{'){
                $outer_curly_depth++;
            }
            elseif($current === '}}'){
                $outer_curly_depth--;
            }
            elseif(
                $current === '\'' &&
                (
                    $previous !== '\\' ||
                    (
                        $previous === '\\' &&
                        $previous_previous === '\\'
                    )
                ) &&
                $is_single_quote === false &&
                $is_double_quote === false
            ){
                $is_single_quote = true;
            }
            elseif(
                $current === '\'' &&
                (
                    $previous !== '\\' ||
                    (
                        $previous === '\\' &&
                        $previous_previous === '\\'
                    )
                ) &&
                $is_single_quote === true &&
                $is_double_quote === false
            ){
                $is_single_quote = false;
            }
            elseif(
                $current === '"' &&
                (
                    $previous !== '\\' ||
                    (
                        $previous === '\\' &&
                        $previous_previous === '\\'
                    )
                ) &&
                $is_single_quote === false &&
                $is_double_quote === false
            ){
                $is_double_quote = true;
            }
            elseif(
                $current === '"' &&
                (
                    $previous !== '\\' ||
                    (
                        $previous === '\\' &&
                        $previous_previous === '\\'
                    )
                ) &&
                $is_single_quote === false &&
                $is_double_quote === true
            ){
                $is_double_quote = false;
            }
            elseif(
                $current === '"' &&
                $previous === '\\' &&
                $is_single_quote === false &&
                $is_double_quote_backslash === false
            ){
                $is_double_quote_backslash = true;
            }
            elseif(
                $current === '"' &&
                $previous === '\\' &&
                $is_single_quote === false &&
                $is_double_quote_backslash === true
            ){
                $is_double_quote_backslash = false;
            }
            elseif(
                $current === '[' &&
                $is_single_quote === false &&
                $is_double_quote === false &&
                $is_double_quote_backslash === false
            ){
                $is_array = true;
                $array_depth++;
            }
            elseif(
                $current === ']' &&
                $is_single_quote === false &&
                $is_double_quote === false &&
                $is_double_quote_backslash === false
            ){
                $array_depth--;
                if($array_depth === 0){
                    $is_array = false;
                    $is_modifier = false;
                }
            }
            elseif(
                $current === '|' &&
                $previous !== '|' &&
                $next !== '|' &&
                $is_single_quote === false &&
                $is_double_quote === false &&
                $is_double_quote_backslash === false &&
                (
                    $set_depth === $set_depth_modifier ||
                    $set_depth_modifier === false
                ) &&
                (
                    $array_depth === $array_depth_modifier ||
                    $array_depth_modifier === false
                )
            ){
                if($is_argument !== false){
                    foreach($argument_array as $argument_nr => $array){
                        $argument_value = Cast::define(
                            $object,
                            $flags,
                            $options,
                            [
                                'string' => $argument[$argument_nr],
                                'array' => $array
                            ]
                        );
                        $argument_value = Token::value(
                            $object,
                            $flags,
                            $options,
                            $argument_value,
                            $tag
                        );
                        $argument_array[$argument_nr] = $argument_value;
                    }
                    d($modifier_string);
                    d($modifier_name);
                    d($argument_array);
                    $input['array'][$is_variable]['modifier'][] = [
                        'string' => $modifier_string,
                        'name' => $modifier_name,
                        'argument' => $argument_array
                    ];
                    if(array_key_exists('modifier', $input)){
                        foreach($input['modifier'] as $index => $modifier){
                            $input['array'][$is_variable]['modifier'][] = $modifier;
                        }
                        unset($input['modifier']);
                    }
                    //check this
                    for($index = $is_variable + 1; $index < $nr; $index++){
                        $input['array'][$index] = null;
                    }
                    $modifier_name = '';
                    $modifier_string = '';
                    $is_argument = false;
                    $argument_array = [];
                    $argument = [];
                    $argument_nr = -1;
                }
                elseif($is_modifier !== false){
//                    d($modifier_string);
//                    d($modifier_name);
                    $input['array'][$is_variable]['modifier'][] = [
                        'string' => $modifier_string,
                        'name' => $modifier_name,
                        'argument' => []
                    ];
                    for($index = $is_variable + 1; $index <= $nr; $index++){
                        $input['array'][$index] = null;
                    }
                    $modifier_name = '';
                    $modifier_string = '';
                    $is_argument = false;
                    $argument_array = [];
                    $argument = [];
                    $argument_nr = -1;
                }
                elseif($is_variable !== false){
                    $is_modifier = true;
                }
            }
            elseif(
                $current === ':' &&
                $previous !== ':' &&
                $next !== ':' &&
                $is_single_quote === false &&
                $is_double_quote === false &&
                $is_double_quote_backslash === false &&
                (
                    $set_depth === $set_depth_modifier ||
                    $set_depth_modifier === false
                ) &&
                (
                    $array_depth === $array_depth_modifier ||
                    $array_depth_modifier === false
                )
            ){
                if($is_modifier !== false){
                    $is_argument = false; //route
                }
                $argument_nr++;
            }
            elseif(
                $current === ',' &&
                $is_single_quote === false &&
                $is_double_quote === false &&
                $is_double_quote_backslash === false &&
                (
                    $set_depth === $set_depth_modifier ||
                    $set_depth_modifier === false
                ) &&
                (
                    $array_depth === $array_depth_modifier ||
                    $array_depth_modifier === false
                )
            ){
                if(
                    $is_variable !== false &&
                    $is_modifier !== false
                ){
                    if($is_argument !== false){
                        foreach($argument_array as $argument_nr => $array){
                            if(array_key_exists('string', $array)){
                                continue;
                            }
                            if(array_key_exists('type', $array)){
                                continue;
                            }
                            $argument_value = Cast::define(
                                $object,
                                $flags,
                                $options,
                                [
                                    'string' => $argument[$argument_nr],
                                    'array' => $array
                                ]
                            );
                            $argument_value = Token::value(
                                $object,
                                $flags,
                                $options,
                                $argument_value,
                                $tag
                            );
                            $argument_array[$argument_nr] = $argument_value;
                        }
                        d($modifier_string);
                        d($modifier_name);
                        d($argument_array);
                        $input['array'][$is_variable]['modifier'][] = [
                            'string' => $modifier_string,
                            'name' => $modifier_name,
                            'argument' => $argument_array
                        ];
                        if(
                            $is_array === true &&
                            $set_depth === $set_depth_modifier &&
                            $array_depth === $array_depth_modifier
                         ){
                            //keep the comma
                            for($index = $is_variable + 1; $index < $nr; $index++){
                                $input['array'][$index] = null;
                            }
                            //end of modifier
                            $modifier_name = '';
                            $modifier_string = '';
                            $is_argument = false;
                            $is_variable = false;
                            $is_modifier = false;
                            $argument_array = [];
                            $argument = [];
                            $argument_nr = -1;
                            $set_depth_modifier = false;
                            $array_depth_modifier = false;
                        } else {
                            //remove the comma
                            for($index = $is_variable + 1; $index <= $nr; $index++){
                                $input['array'][$index] = null;
                            }
                        }
                    }
                    elseif($is_modifier !== false){
                        $input['array'][$is_variable]['modifier'][] = [
                            'string' => $modifier_string,
                            'name' => $modifier_name,
                            'argument' => []
                        ];
                        //check this
                        for($index = $is_variable + 1; $index <= $nr; $index++){
                            $input['array'][$index] = null;
                        }
                    }
                }
            }
            elseif(
                $current !== null &&
                is_array($char) &&
                $char['type'] === 'variable' &&
                $is_single_quote === false &&
                $is_double_quote === false &&
                $is_variable === false
            ){
                $is_variable = $nr;
            }
            if($is_modifier === true){
                $modifier_string .= $current;
            }
            if(
                $is_modifier === true &&
                $is_argument === false
            ){
                if(
                    !in_array(
                        $current,
                        [
                            ' ',
                            "\t",
                            "\n",
                            "\r",
                            ':',
                            '|',
                        ],
                        true
                    )
                ){
                    $modifier_name .= $current;
                    if($set_depth_modifier === false){
                        if($set_depth === 0){
                            $set_depth_modifier = 0;
                        } else {
                            $set_depth_modifier = $set_depth;
                        }
                    }
                    if($array_depth_modifier === false){
                        if($array_depth === 0){
                            $array_depth_modifier = 0;
                        } else {
                            $array_depth_modifier = $array_depth;
                        }
                    }
                }
                elseif(
                    in_array(
                        $current,
                        [
                            ':'
                        ],
                        true
                    ) &&
                    $is_single_quote === false &&
                    $is_double_quote === false &&
                    $is_double_quote_backslash === false
                ){
                    $is_argument = true;
                    if($set_depth_modifier === false){
                        if($set_depth === 0){
                            $set_depth_modifier = 0;
                        } else {
                            $set_depth_modifier = $set_depth;
                        }
                    }
                    if($array_depth_modifier === false){
                        if($array_depth === 0){
                            $array_depth_modifier = 0;
                        } else {
                            $array_depth_modifier = $array_depth;
                        }
                    }
//                    $argument_nr++; //already happened
                }
            }
            elseif(
                $is_argument
            ){
                if(
                    $current === ':' &&
                    $is_single_quote === false &&
                    $is_double_quote === false &&
                    $is_double_quote_backslash === false
                ){
                    if(
                        $set_depth === $set_depth_modifier ||
                        $set_depth_modifier === false
                    ){
                        $argument_nr++;
                    } else {
                        if(!array_key_exists($argument_nr, $argument_array)){
                            $argument_array[$argument_nr] = [];
                            $argument[$argument_nr] = '';
                        }
                        $argument[$argument_nr] .= $current;
                        $argument_array[$argument_nr][] = $char;
                    }
                } else {
                    if(!array_key_exists($argument_nr, $argument_array)){
                        $argument_array[$argument_nr] = [];
                        $argument[$argument_nr] = '';
                    }
                    $argument[$argument_nr] .= $current;
                    $argument_array[$argument_nr][] = $char;
                }
            }
        }
        if(
            $is_variable !== false &&
            $is_modifier !== false
        ){
            if($is_argument !== false){
                foreach($argument_array as $argument_nr => $array){
                    $argument_value = Cast::define(
                        $object,
                        $flags,
                        $options,
                        [
                            'string' => $argument[$argument_nr],
                            'array' => $array
                        ]
                    );
                    $argument_value = Token::value(
                        $object,
                        $flags,
                        $options,
                        $argument_value,
                        $tag
                    );
                    $argument_array[$argument_nr] = $argument_value;
                }
                $input['array'][$is_variable]['modifier'][] = [
                    'string' => $modifier_string,
                    'name' => $modifier_name,
                    'argument' => $argument_array
                ];
                for($index = $is_variable + 1; $index <= $nr; $index++){
                    $input['array'][$index] = null;
                }
            }
            elseif($is_modifier !== false){
                $input['array'][$is_variable]['modifier'][] = [
                    'string' => $modifier_string,
                    'name' => $modifier_name,
                    'argument' => []
                ];
                for($index = $is_variable + 1; $index <= $nr; $index++){
                    $input['array'][$index] = null;
                }
            }
        }
        /* wrong
        if($is_variable !== false){
            for($index = $is_variable + 1; $index <= $nr; $index++){
                $input['array'][$index] = null;
            }
        }
        */
        return $input;
    }
}