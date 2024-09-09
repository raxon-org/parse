<?php
namespace Package\Raxon\Parse\Service;

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
        if(is_int($input['array'])){
            trace();
            ddd($input);
        }
        foreach($input['array'] as $nr => $char){
            if(is_array($char)){
                continue;
            }
            if(
                $char === '\'' &&
                $is_single_quote === false
            ){
                $is_single_quote = true;
            }
            elseif(
                $char === '\'' &&
                $is_single_quote === true
            ){
                $is_single_quote = false;
            }
            if(
                (
                    $is_single_quote === false ||
                    (
                        $char === '\'' &&
                        $is_single_quote === true
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
                    $previous_char = $input['array'][$previous_nr]['value'];
                    $symbol = $previous_char . $char;
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
                        case '...':
                        case '===':
                        case '<<=':
                        case '=>>':
                        case '!==':
                        case '!!!':
                        case '!!!!':
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
                } else {
                    $input['array'][$nr] = [
                        'type' => 'symbol',
                        'value' => $char,
                    ];
                }
            }
            $previous_nr = $nr;
        }
        return $input;
    }
}