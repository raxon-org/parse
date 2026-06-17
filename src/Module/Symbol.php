<?php
namespace Raxon\Parse\Module;

use Raxon\App;

use Raxon\Module\Core;
use Raxon\Module\File;

use Exception;
class Symbol
{
    /**
     * @throws Exception
     */
    public static function define(App $object, $flags, $options, $input=[]): array
    {
        if(!is_array($input)){
            return $input;
        }
        if(array_key_exists('array', $input) === false){
            return $input;
        }
        $previous_nr = false;
        $is_single_quote = false;
        $is_double_quote = false;
        $is_double_quote_backslash = false;
        if(!is_array($input['array'])){
            throw new Exception('Not an array');
        }
        $skip = 0;
        $before = [];
        foreach($input['array'] as $nr => $char){
            if(!is_int($nr)){
                trace();
                d($input);
                throw new Exception('Index not an integer');
            }
            $previous_nr = $nr - 1;
            if($previous_nr < 0){
                $previous_nr = null;
                $previous = null;
            } else {
                $previous = Token::item($input, $previous_nr);
            }
            $previous_2x = Token::item($input, $nr - 2);
            $previous_3x = Token::item($input, $nr - 3);
            $previous_4x = Token::item($input, $nr - 4);
            $previous_5x = Token::item($input, $nr - 5);
            $next = Token::item($input, $nr + 1);
            $next_next = Token::item($input, $nr + 2);
            if($skip > 0){
                $skip -= 1;
                $before[] = $char;
                continue;
            }
            if(is_array($char)){
                $before[] = $char;
                continue;
            }
            if(
                $char === '\'' &&
                Symbol::check_previous($before) &&
                $is_single_quote === false &&
                $is_double_quote === false &&
                $is_double_quote_backslash === false
            ){
                $is_single_quote = $nr;
                $before = [];
                continue;
            }
            elseif(
                $char === '\'' &&
                Symbol::check_previous($before) &&
                $is_single_quote !== false &&
                $is_double_quote === false &&
                $is_double_quote_backslash === false
            ){
                $before = [];
                $string = '';
                for($i = $is_single_quote; $i <= $nr; $i++){
                    if(is_array($input['array'][$i])){
                        if(array_key_exists('value', $input['array'][$i])){
                            $string .= $input['array'][$i]['value'];
                        } else {
                            throw new Exception('Not implemented, no value');
                        }
                    } else {
                        $string .= $input['array'][$i];
                    }

                    $input['array'][$i] = null;
                }

                $input['array'][$is_single_quote] = [
                    'type' => 'string',
                    'value' => $string,
                    'execute' => substr($string, 1, -1),
                    'is_single_quoted' => true
                ];
                $is_single_quote = false;
                continue;
            }
            elseif(
                $char === '"' &&
                Symbol::check_previous($before) &&
                $is_single_quote === false &&
                $is_double_quote === false &&
                $is_double_quote_backslash === false
            ){
                $before = [];
                $is_double_quote = $nr;
                continue;
            }
            elseif(
                $char === '"' &&
                Symbol::check_previous($before) &&
                $is_single_quote === false &&
                $is_double_quote !== false &&
                $is_double_quote_backslash === false
            ){
                $before = [];
                $string = '';
                for($i = $is_double_quote; $i <= $nr; $i++){
                    if(
                        is_array($input['array'][$i]) &&
                        array_key_exists('execute', $input['array'][$i])
                    ){
                        $string .= $input['array'][$i]['execute'];
                    }
                    elseif(
                        is_array($input['array'][$i]) &&
                        array_key_exists('value', $input['array'][$i])
                    ){
                        $string .= $input['array'][$i]['value'];
                    } else {
                        $string .= $input['array'][$i];
                    }
                    $input['array'][$i] = null;
                }
                $execute = substr($string, 1, -1);
                //from \\ to \ and from \" to " not needed by is_single_quote=true and to early here !!!
//                $execute = str_replace('\"', '"', $execute);
//                $execute = str_replace('\\\\', '\\', $execute);
                $input['array'][$is_double_quote] = [
                    'type' => 'string',
                    'value' => $string,
                    'execute' => $execute,
                    'is_double_quoted' => true
                ];
                $is_double_quote = false;
                continue;
            }
            elseif(
                $char === '"' &&
                $previous === '\\' &&
                Symbol::check_previous($before) &&
                $is_single_quote === false &&
                $is_double_quote === false &&
                $is_double_quote_backslash === false
            ){
                $before = [];
                $is_double_quote_backslash = $previous_nr;
                // $input['array'][$previous_nr] = null;
                continue;
            }
            elseif(
                $char === '"' &&
                $previous === '\\' &&
                Symbol::check_previous($before) &&
                $is_single_quote === false &&
                $is_double_quote === false &&
                $is_double_quote_backslash !== false
            ){
                $before = [];
                $string = '';
                for($i = $is_double_quote_backslash; $i <= $nr; $i++){
                    if(is_array($input['array'][$i])){
                        if(array_key_exists('value', $input['array'][$i])){
                            $string .= $input['array'][$i]['value'];
                        } else {
                            throw new Exception('Not implemented, no value');
                        }
                    } else {
                        $string .= $input['array'][$i];
                    }
                    $input['array'][$i] = null;
                }
                $execute = substr($string, 2, -2);
                //from \\ to \ and from \" to " not needed by is_single_quote=true and too early here !!!
//                $execute = str_replace('\"', '"', $execute);
//                $execute = str_replace('\\\\', '\\', $execute);
                $input['array'][$is_double_quote_backslash] = [
                    'type' => 'string',
                    'value' => $string,
//                    'execute' => '"' . substr($string, 2, -2) . '"', // was: substr($string, 2, -2),
                    'execute' => $execute, // was: above
                    'is_double_quoted' => true,
                    'is_backslash' => true
                ];
                $is_double_quote_backslash = false;
                continue;
            }
            if(
                (
                    (
                        $is_single_quote === false &&
                        $is_double_quote === false &&
                        $is_double_quote_backslash === false
                    )
                    ||
                    (
                        $char === '\'' &&
                        $is_single_quote !== false
                    )
                )
                &&
                in_array(
                    $char,
                    [
                        '`',
                        '~',
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
                        '_',
                        '=',
                        '+',
                        '[',
                        ']',
                        '{',
                        '}',
                        '|',
                        '\\',
                        ':',
                        ';',
                        '"',
                        "'",
                        ',',
                        '.',
                        '<',
                        '>',
                        '/',
                        '?',
                    ],
                    true
                )
            ){
                if(
                    $previous_nr !== false &&
                    array_key_exists($previous_nr, $input['array']) &&
                    is_array($input['array'][$previous_nr]) &&
                    array_key_exists('type', $input['array'][$previous_nr]) &&
                    $input['array'][$previous_nr]['type'] === 'symbol'
                ){
                    if(is_array($previous)){
                        $previous = $previous['value'] ?? '';
                    }
                    $symbol = $previous . $char;
                    switch($symbol) {
                        case '{{':
                        case '}}':
                        case '++':
                        case '--':
                        case '<<':
                        case '>>':
                        case '<=':
                        case '>=':
                        case '==':
                        case '!=':
                        case '!!':
                        case '??':
                        case '&&':
                        case '||':
                        case '+=':
                        case '-=':
                        case '*=':
                        case '/=':
                        case '.=':
                        case '=>':
                        case '->':
                        case '::':
                        case '..':
                        case '/*':
                        case '*/':
                        case '//':
                        case '\\"':
                        case '|>':
                            $input['array'][$previous_nr] = [
                                'type' => 'symbol',
                                'value' => $symbol,
                            ];
                            $input['array'][$nr] = null;
                            break;
                        default:
                            $input['array'][$nr] = [
                                'type' => 'symbol',
                                'value' => $char,
                            ];
                    }
                    $symbol = $previous . $char . $next;
                    switch($symbol) {
                        case '...':
                        case '===':
                        case '<<=':
                        case '=>>':
                        case '!==':
                        case '!!!':
                        case '/**':
                        case '**/':
                            $input['array'][$previous_nr] = [
                                'type' => 'symbol',
                                'value' => $symbol,
                            ];
                            $input['array'][$nr] = null;
                            $input['array'][$nr + 1] = null;
                            $skip += 1;
                            break;
                    }
                    $symbol = $previous . $char . $next . $next_next;
                    switch ($symbol){
                        case '!!!!':
                            $input['array'][$previous_nr] = [
                                'type' => 'symbol',
                                'value' => $symbol,
                            ];
                            $input['array'][$nr] = null;
                            $input['array'][$nr + 1] = null;
                            $input['array'][$nr + 2] = null;
                            $skip += 2;
                            break;
                        case '{{/*':
                        case '*/}}':
                            $input['array'][$previous_nr] = [
                                'type' => 'symbol',
                                'value' => $previous . $char,
                            ];
                            $input['array'][$nr] = [
                                'type' => 'symbol',
                                'value' => $next . $next_next,
                            ];
                            $input['array'][$nr + 1] = null;
                            $input['array'][$nr + 2] = null;
                            $skip += 2;
                            break;
                    }
                } else {
                    $input['array'][$nr] = [
                        'type' => 'symbol',
                        'value' => $char,
                    ];
                }
            }
            $before[] = $char;
        }
        return $input;
    }

    public static function check_previous(array $before=[], bool $is_quote_backslash=false): bool
    {
        if($is_quote_backslash === true){
            array_pop($before);
        }
        $list = array_reverse($before);
        $count = 0;
        foreach($list as $char){
            if(is_array($char)){
                if(
                    array_key_exists('type', $char) &&
                    $char['type'] === 'symbol' &&
                    $char['value'] === '\\'
                ){
                    $count++;
                }
            }
            elseif(
                is_string($char) &&
                $char === '\\'
            ){
                $count++;
            } else {
                break;
            }
        }
        if($count % 2 === 0){
            return true;
        }
        return false;
    }
}