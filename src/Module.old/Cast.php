<?php
namespace Raxon\Parse\Module;

use Raxon\App;

use Raxon\Module\Core;
use Raxon\Module\File;

use Exception;
class Cast
{
    public static function define(App $object, $flags, $options, $input=[]): array
    {
        if(!is_array($input)){
            return $input;
        }
        if(array_key_exists('array', $input) === false){
            return $input;
        }
        $is_collect = false;
        $define = '';
        foreach($input['array'] as $nr => $char){
            if(
                is_array($char) &&
                array_key_exists('value', $char) &&
                $char['value'] === '('
            ){
                $is_collect = $nr;
            }
            elseif(
                is_array($char) &&
                array_key_exists('value', $char) &&
                $char['value'] === ')'
            ){
                if(mb_strlen($define) > 0){
                    $is_define = false;
                    switch(mb_strtolower($define)){
                        case 'int':
                        case 'integer':
                            $input['array'][$is_collect + 1] = [
                                'value' => $define,
                                'type' => 'cast',
                                'cast' => 'integer'
                            ];
                            $is_define = true;
                        break;
                        case 'float':
                        case 'double':
                            $input['array'][$is_collect + 1] = [
                                'value' => $define,
                                'type' => 'cast',
                                'cast' => 'float'
                            ];
                            $is_define = true;
                        break;
                        case 'boolean':
                        case 'bool':
                            $input['array'][$is_collect + 1] = [
                                'value' => $define,
                                'type' => 'cast',
                                'cast' => 'boolean'
                            ];
                            $is_define = true;
                        break;
                        case 'string':
                            $input['array'][$is_collect + 1] = [
                                'value' => $define,
                                'type' => 'cast',
                                'cast' => 'string'
                            ];
                            $is_define = true;
                        break;
                        case 'array':
                            $input['array'][$is_collect + 1] = [
                                'value' => $define,
                                'type' => 'cast',
                                'cast' => 'array'
                            ];
                            $is_define = true;
                        break;
                        case 'object':
                            $input['array'][$is_collect + 1] = [
                                'value' => $define,
                                'type' => 'cast',
                                'cast' => 'object'
                            ];
                            $is_define = true;
                        break;
                        case 'clone':
                            $input['array'][$is_collect + 1] = [
                                'value' => $define,
                                'type' => 'cast',
                                'cast' => 'clone'
                            ];
                            $is_define = true;
                        break;
                    }
                    if($is_define){
                        for($i = $is_collect + 2; $i < $nr; $i++){
                            $input['array'][$i] = null;
                        }
                    }
                    $define = '';
                }
                $is_collect = false;
            }
            elseif(
                $is_collect !== false &&
                !is_array($char)
            ){
                if(
                    in_array(
                        $char,
                        [
                            ' ',
                            "\t",
                            "\n",
                            "\r",
                        ],
                        true
                    )
                ){
                    continue;
                }
                $define .= $char;
            }
            elseif(
                $is_collect !== false &&
                is_array($char)
            ){
                $is_collect = false;
                $define = '';
            }
        }
        return $input;
    }
}