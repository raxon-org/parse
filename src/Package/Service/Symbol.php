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
        $skip = 0;
        d($input['array']);
        trace();
        foreach($input['array'] as $nr => $char){
            $previous_nr = $nr - 1;
            if($previous_nr < 0){
                $previous_nr = null;
                $previous = null;
            } else {
                $previous = $input['array'][$previous_nr];
            }
            $next = $input['array'][$nr + 1] ?? null;
            $next_next = $input['array'][$nr + 2] ?? null;
            if($skip > 0){
                $skip -= 1;
                continue;
            }
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
                    if(is_array($previous)){
                        $previous = $previous['value'] ?? '';
                    }
                    $symbol = $previous . $char;
                    d($symbol);
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