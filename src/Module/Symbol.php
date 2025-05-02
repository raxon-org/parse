<?php
namespace Raxon\Parse\Module;

use Raxon\App;

use Raxon\Module\Core;
use Raxon\Module\File;

use Exception;
class Symbol
{
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
        if(is_int($input['array'])){
            trace();
            ddd($input);
        }
        if(is_string($input['array'])){
            trace();
            ddd($input);
        }
        $skip = 0;
        foreach($input['array'] as $nr => $char){
            if(!is_int($nr)){
                trace();
                ddd($input);
            }
            $previous_nr = $nr - 1;
            if($previous_nr < 0){
                $previous_nr = null;
                $previous = null;
            } else {
                $previous = Token::item($input, $previous_nr);
            }
            $next = Token::item($input, $nr + 1);
            $next_next = Token::item($input, $nr + 2);
            if($skip > 0){
                $skip -= 1;
                continue;
            }
            if(is_array($char)){
                continue;
            }
            if(
                $char === '\'' &&
                $previous !== '\\' &&
                $is_single_quote === false &&
                $is_double_quote === false &&
                $is_double_quote_backslash === false
            ){
                $is_single_quote = $nr;
                continue;
            }
            elseif(
                $char === '\'' &&
                $previous !== '\\' &&
                $is_single_quote !== false &&
                $is_double_quote === false &&
                $is_double_quote_backslash === false
            ){
                $string = '';
                for($i = $is_single_quote; $i <= $nr; $i++){
                    $string .= $input['array'][$i];
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
                $previous !== '\\' &&
                $is_single_quote === false &&
                $is_double_quote === false
            ){
                $is_double_quote = $nr;
                continue;
            }
            elseif(
                $char === '"' &&
                $previous !== '\\' &&
                $is_single_quote === false &&
                $is_double_quote !== false
            ){
                $string = '';
                for($i = $is_double_quote; $i <= $nr; $i++){
                    $string .= $input['array'][$i];
                    $input['array'][$i] = null;
                }
                $input['array'][$is_double_quote] = [
                    'type' => 'string',
                    'value' => $string,
                    'execute' => substr($string, 1, -1),
                    'is_double_quoted' => true
                ];
                $is_double_quote = false;
                continue;
            }
            elseif(
                $char === '"' &&
                $previous === '\\' &&
                $is_single_quote === false
            ){
                $is_double_quote_backslash = $nr;
                continue;
            }
            elseif(
                $char === '"' &&
                $previous === '\\' &&
                $is_single_quote === false &&
                $is_double_quote_backslash !== false
            ){
                $string = '';
                for($i = $is_double_quote_backslash; $i <= $nr; $i++){
                    $string .= $input['array'][$i];
                    $input['array'][$i] = null;
                }
                $input['array'][$is_double_quote_backslash] = [
                    'type' => 'string',
                    'value' => $string,
                    'execute' => substr($string, 2, -2),
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
        }
        return $input;
    }
}