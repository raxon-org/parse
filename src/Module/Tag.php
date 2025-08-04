<?php
namespace Raxon\Parse\Module;

use Raxon\App;

use Raxon\Module\Core;
use Raxon\Module\File;

use Exception;
class Tag
{
    public static function define(App $object, $flags, $options, $input=''): array
    {
        if(!is_string($input)){
            return [];
        }
        $length = mb_strlen($input);
        $start = microtime(true);
        $split = mb_str_split($input, 1);
        $curly_count = 0;
        $line = 1;
        $column = [];
        $column[$line] = 1;
        $tag = false;
        $tag_list = [];
        $is_literal = false;
        $is_single_quoted = false;
        $is_double_quoted = false;
        $is_double_quoted_backslash = false;
        $is_comment = false;
        $is_comment_multiline = false;
        $is_tag_in_double_quoted = false;
        $next = false;
        $previous = false;
        $text = '';
        $skip = 0;
        foreach($split as $nr => $char){
            if($skip > 0){
                $skip--;
                continue;
            }            
            $previous = $split[$nr - 1] ?? null;
            $next = $split[$nr + 1] ?? null;
            $next_next = $split[$nr + 2] ?? null;
            $next_next_next = $split[$nr + 3] ?? null;
            if($char === "\n"){
                $line++;
                $column[$line] = 1;
                if(
                    $is_comment === true &&
                    $is_comment_multiline === false
                ){
                    $is_comment = false;
                }
            }
            if(                
                $char === '\''
            ){
                d($tag);
                d($nr);
            }

            if(
                $tag === false &&
                $char === '\'' &&
                $is_single_quoted === false &&
                $is_double_quoted === false &&
                $is_double_quoted_backslash === false &&
                $previous !== '\\'
            ){
                d($nr);
                d($text);
                if($text !== ''){                    
                    $explode = explode("\n", $text);
                    $count = count($explode);
                    $explode_tag = explode("\n", $tag);
                    if($count > 1){
                        $length_start = mb_strlen($explode[0]);
                        $record = [
                            'text' => $text,
                            'is_multiline' => true,
                            'line' => [
                                'start' => $line - $count + 1,
                                'end' => $line
                            ],
                            'length' => [
                                'start' => $length_start,
                                'end' => mb_strlen($explode[$count - 1])
                            ],
                            'column' => [
                                ($line - $count + 1) => [
                                    'start' => $column[$line - $count + 1] - $length_start,
                                    'end' => $column[$line - $count + 1]
                                ],
                                $line => [
                                    'start' => $column[$line] - mb_strlen($explode[$count - 1]) - mb_strlen($explode_tag[0]),
                                    'end' => $column[$line] - mb_strlen($explode_tag[0])
                                ]
                            ]
                        ];
                        if(empty($tag_list[$line - $count + 1])){
                            $tag_list[$line - $count + 1] = [];
                        }
                        $tag_list[$line - $count + 1][] = $record;
                    } else {
                        $length_start = mb_strlen($explode[0]);
                        $record = [
                            'text' => $text,
                            'line' => $line,
                            'length' => $length_start,
                            'column' => [
                                'start' => $column[$line] - $length_start - mb_strlen($explode_tag[0]),
                                'end' => $column[$line] - mb_strlen($explode_tag[0])
                            ]
                        ];
                        if(empty($tag_list[$line])){
                            $tag_list[$line] = [];
                        }
                        $tag_list[$line][] = $record;
                    }
                    $text = '';
                }
                $is_single_quoted = true;
            }
            elseif(
                $tag === false &&
                $char === '\'' &&
                $is_single_quoted === true &&
                $is_double_quoted === false &&
                $is_double_quoted_backslash === false &&
                $previous !== '\\'
            ){
                d($nr);
                d($text);
                if($text !== ''){
                    $text .= $char;
                    $explode = explode("\n", $text);
                    $count = count($explode);
                    $explode_tag = explode("\n", $tag);
                    if($count > 1){
                        $length_start = mb_strlen($explode[0]);
                        $record = [
                            'text' => $text,
                            'is_multiline' => true,
                            'line' => [
                                'start' => $line - $count + 1,
                                'end' => $line
                            ],
                            'length' => [
                                'start' => $length_start,
                                'end' => mb_strlen($explode[$count - 1])
                            ],
                            'column' => [
                                ($line - $count + 1) => [
                                    'start' => $column[$line - $count + 1] - $length_start,
                                    'end' => $column[$line - $count + 1]
                                ],
                                $line => [
                                    'start' => $column[$line] - mb_strlen($explode[$count - 1]) - mb_strlen($explode_tag[0]),
                                    'end' => $column[$line] - mb_strlen($explode_tag[0])
                                ]
                            ]
                        ];
                        if(empty($tag_list[$line - $count + 1])){
                            $tag_list[$line - $count + 1] = [];
                        }
                        $tag_list[$line - $count + 1][] = $record;
                    } else {
                        $length_start = mb_strlen($explode[0]);
                        $record = [
                            'text' => $text,
                            'line' => $line,
                            'length' => $length_start,
                            'column' => [
                                'start' => $column[$line] - $length_start - mb_strlen($explode_tag[0]),
                                'end' => $column[$line] - mb_strlen($explode_tag[0])
                            ]
                        ];
                        if(empty($tag_list[$line])){
                            $tag_list[$line] = [];
                        }
                        $tag_list[$line][] = $record;
                    }
                    $text = '';
                    $is_single_quoted = false;
                    d($nr);
                    continue;
                }
                $is_single_quoted = false;
            }           
            elseif(
                $tag === false &&
                $char === '"' &&
                $is_single_quoted === false &&
                $is_double_quoted === false &&
                $is_double_quoted_backslash === false &&
                $previous !== '\\'
            ){
                if($text !== ''){
                    $explode = explode("\n", $text);
                    $count = count($explode);
                    $explode_tag = explode("\n", $tag);
                    if($count > 1){
                        $length_start = mb_strlen($explode[0]);
                        $record = [
                            'text' => $text,
                            'is_multiline' => true,
                            'line' => [
                                'start' => $line - $count + 1,
                                'end' => $line
                            ],
                            'length' => [
                                'start' => $length_start,
                                'end' => mb_strlen($explode[$count - 1])
                            ],
                            'column' => [
                                ($line - $count + 1) => [
                                    'start' => $column[$line - $count + 1] - $length_start,
                                    'end' => $column[$line - $count + 1]
                                ],
                                $line => [
                                    'start' => $column[$line] - mb_strlen($explode[$count - 1]) - mb_strlen($explode_tag[0]),
                                    'end' => $column[$line] - mb_strlen($explode_tag[0])
                                ]
                            ]
                        ];
                        if(empty($tag_list[$line - $count + 1])){
                            $tag_list[$line - $count + 1] = [];
                        }
                        $tag_list[$line - $count + 1][] = $record;
                    } else {
                        $length_start = mb_strlen($explode[0]);
                        $record = [
                            'text' => $text,
                            'line' => $line,
                            'length' => $length_start,
                            'column' => [
                                'start' => $column[$line] - $length_start - mb_strlen($explode_tag[0]),
                                'end' => $column[$line] - mb_strlen($explode_tag[0])
                            ]
                        ];
                        if(empty($tag_list[$line])){
                            $tag_list[$line] = [];
                        }
                        $tag_list[$line][] = $record;
                    }
                    $text = '';
                }
                $is_double_quoted = true;
            }
            elseif(
                $tag === false &&
                $char === '"' &&
                $is_single_quoted === false &&
                $is_double_quoted === true &&
                $is_double_quoted_backslash === false &&
                $previous !== '\\'
            ){            
                if($text !== ''){
                    $text .= $char;
                    $explode = explode("\n", $text);
                    $count = count($explode);
                    $explode_tag = explode("\n", $tag);
                    if($count > 1){
                        $length_start = mb_strlen($explode[0]);
                        $record = [
                            'text' => $text,
                            'is_multiline' => true,
                            'line' => [
                                'start' => $line - $count + 1,
                                'end' => $line
                            ],
                            'length' => [
                                'start' => $length_start,
                                'end' => mb_strlen($explode[$count - 1])
                            ],
                            'column' => [
                                ($line - $count + 1) => [
                                    'start' => $column[$line - $count + 1] - $length_start,
                                    'end' => $column[$line - $count + 1]
                                ],
                                $line => [
                                    'start' => $column[$line] - mb_strlen($explode[$count - 1]) - mb_strlen($explode_tag[0]),
                                    'end' => $column[$line] - mb_strlen($explode_tag[0])
                                ]
                            ]
                        ];
                        if(empty($tag_list[$line - $count + 1])){
                            $tag_list[$line - $count + 1] = [];
                        }
                        $tag_list[$line - $count + 1][] = $record;
                    } else {
                        $length_start = mb_strlen($explode[0]);
                        $record = [
                            'text' => $text,
                            'line' => $line,
                            'length' => $length_start,
                            'column' => [
                                'start' => $column[$line] - $length_start - mb_strlen($explode_tag[0]),
                                'end' => $column[$line] - mb_strlen($explode_tag[0])
                            ]
                        ];
                        if(empty($tag_list[$line])){
                            $tag_list[$line] = [];
                        }
                        $tag_list[$line][] = $record;
                    }
                    $text = '';
                    $is_double_quoted = false;
                    continue;
                }
                $is_double_quoted = false;
            }
            elseif(
                $tag === false &&
                $char === '"' &&
                $is_single_quoted === false &&
                $is_double_quoted_backslash === false &&
                $previous === '\\'
            ){
                if($text !== ''){                    
                    $text = substr($text, 0, -1);
                    $explode = explode("\n", $text);
                    $count = count($explode);
                    $explode_tag = explode("\n", $tag);
                    if($count > 1){
                        $length_start = mb_strlen($explode[0]);
                        $record = [
                            'text' => $text,
                            'is_multiline' => true,
                            'line' => [
                                'start' => $line - $count + 1,
                                'end' => $line
                            ],
                            'length' => [
                                'start' => $length_start,
                                'end' => mb_strlen($explode[$count - 1])
                            ],
                            'column' => [
                                ($line - $count + 1) => [
                                    'start' => $column[$line - $count + 1] - $length_start,
                                    'end' => $column[$line - $count + 1]
                                ],
                                $line => [
                                    'start' => $column[$line] - mb_strlen($explode[$count - 1]) - mb_strlen($explode_tag[0]),
                                    'end' => $column[$line] - mb_strlen($explode_tag[0])
                                ]
                            ]
                        ];
                        if(empty($tag_list[$line - $count + 1])){
                            $tag_list[$line - $count + 1] = [];
                        }
                        $tag_list[$line - $count + 1][] = $record;
                    } else {
                        $length_start = mb_strlen($explode[0]);
                        $record = [
                            'text' => $text,
                            'line' => $line,
                            'length' => $length_start,
                            'column' => [
                                'start' => $column[$line] - $length_start - mb_strlen($explode_tag[0]),
                                'end' => $column[$line] - mb_strlen($explode_tag[0])
                            ]
                        ];
                        if(empty($tag_list[$line])){
                            $tag_list[$line] = [];
                        }
                        $tag_list[$line][] = $record;
                    }
                    $text = '\\';
                }
                $is_double_quoted_backslash = true;
            }
            elseif(
                $tag === false &&
                $char === '"' &&
                $is_single_quoted === false &&
                $is_double_quoted_backslash === true &&
                $previous === '\\'
            ){
                if($text !== ''){
                    $text .= $char;
                    $explode = explode("\n", $text);
                    $count = count($explode);
                    $explode_tag = explode("\n", $tag);
                    if($count > 1){
                        $length_start = mb_strlen($explode[0]);
                        $record = [
                            'text' => $text,
                            'is_multiline' => true,
                            'line' => [
                                'start' => $line - $count + 1,
                                'end' => $line
                            ],
                            'length' => [
                                'start' => $length_start,
                                'end' => mb_strlen($explode[$count - 1])
                            ],
                            'column' => [
                                ($line - $count + 1) => [
                                    'start' => $column[$line - $count + 1] - $length_start,
                                    'end' => $column[$line - $count + 1]
                                ],
                                $line => [
                                    'start' => $column[$line] - mb_strlen($explode[$count - 1]) - mb_strlen($explode_tag[0]),
                                    'end' => $column[$line] - mb_strlen($explode_tag[0])
                                ]
                            ]
                        ];
                        if(empty($tag_list[$line - $count + 1])){
                            $tag_list[$line - $count + 1] = [];
                        }
                        $tag_list[$line - $count + 1][] = $record;
                    } else {
                        $length_start = mb_strlen($explode[0]);
                        $record = [
                            'text' => $text,
                            'line' => $line,
                            'length' => $length_start,
                            'column' => [
                                'start' => $column[$line] - $length_start - mb_strlen($explode_tag[0]),
                                'end' => $column[$line] - mb_strlen($explode_tag[0])
                            ]
                        ];
                        if(empty($tag_list[$line])){
                            $tag_list[$line] = [];
                        }
                        $tag_list[$line][] = $record;
                    }
                    $text = '';
                    $is_double_quoted_backslash = false;
                    continue;
                }
                $is_double_quoted_backslash = false;
            }
            elseif(
                $char === '{' &&
                $is_single_quoted === false &&
                $is_double_quoted === false &&
                $is_double_quoted_backslash === false
            ){
                if($previous === '{'){
                    $curly_count++;
                }
            }
            elseif(
                $char === '}' &&
                $is_single_quoted === false &&
                $is_double_quoted === false &&
                $is_double_quoted_backslash === false
            ){
                if($previous === '}'){
                    $curly_count--;
                }
            }
            elseif(
                $char === '/' &&
                $next === '/' &&
                $is_single_quoted === false &&
                $is_double_quoted === false &&
                $is_double_quoted_backslash === false &&
                in_array(
                    $previous,
                    [
                        null,
                        ' ',
                        "\n",
                        "\t",
                        '{',
                        '}'
                    ],
                    true
                )
            ){
                $is_comment = true;
            }
            elseif(
                $char === '/' &&
                $next === '*' &&
                $is_single_quoted === false &&
                $is_double_quoted === false &&
                $is_double_quoted_backslash === false
            ){
                $is_comment = true;
                $is_comment_multiline = true;
                d($previous);
                d($nr);
                ddd($split);
            }
            elseif(
                $char === '*' &&
                $next === '/' &&
                $is_single_quoted === false &&
                $is_double_quoted === false &&
                $is_double_quoted_backslash === false &&
                $is_comment_multiline === true
            ){
                $is_comment = false;
                $is_comment_multiline = false;
                if($curly_count >= 1){
                    $skip++;
                    if(
                        in_array(
                            $next_next ,
                            [
                                "\n"
                            ],
                            true
                        )
                    ){
                        $skip++;
                    }
                    continue;
                }
                elseif($curly_count === 0){
                    $skip++;
                    if(
                        in_array(
                            $next_next ,
                            [
                                "\n"
                            ],
                            true
                        )
                    ){
                        $skip++;
                    }
                    continue;
                }
            }
            if(
                $tag === false &&
                $char === '{' &&
                $previous === '{' &&
                $is_comment === false &&
                $is_single_quoted === false && 
                $is_double_quoted === false &&
                $is_double_quoted_backslash === false
            ){
                $tag = '{{';                                
                $text = mb_substr($text, 0, -1);                
            }
            elseif(
                $tag === false &&
                $char === '{' &&
                $previous === '{' &&
                $is_comment === false &&
                $is_single_quoted === false && 
                $is_double_quoted === true &&
                $is_double_quoted_backslash === false
            ){
                $tag = '{{';                                
                $text = mb_substr($text, 0, -1);                
            }
            elseif(
                $tag !== false &&
                $char === '}' &&
                $previous === '}' &&
                $is_comment === false &&
                $curly_count === 0 &&
                $is_single_quoted === false &&
                // $is_double_quoted === false &&
                $is_double_quoted_backslash === false
            ){                
                $tag .= $char;                
                $column[$line]++;
                if($text !== ''){
                    $explode = explode("\n", $text);
                    $count = count($explode);
                    $explode_tag = explode("\n", $tag);
                    if($count > 1){
                        $length_start = mb_strlen($explode[0]);
                        $record = [
                            'text' => $text,
                            'is_multiline' => true,
                            'line' => [
                                'start' => $line - $count + 1,
                                'end' => $line
                            ],
                            'length' => [
                                'start' => $length_start,
                                'end' => mb_strlen($explode[$count - 1])
                            ],
                            'column' => [
                                ($line - $count + 1) => [
                                    'start' => $column[$line - $count + 1] - $length_start,
                                    'end' => $column[$line - $count + 1]
                                ],
                                $line => [
                                    'start' => $column[$line] - mb_strlen($explode[$count - 1]) - mb_strlen($explode_tag[0]),
                                    'end' => $column[$line] - mb_strlen($explode_tag[0])
                                ]
                            ]
                        ];
                        if(empty($tag_list[$line - $count + 1])){
                            $tag_list[$line - $count + 1] = [];
                        }
                        $tag_list[$line - $count + 1][] = $record;
                    } else {
                        $length_start = mb_strlen($explode[0]);
                        $record = [
                            'text' => $text,
                            'line' => $line,
                            'length' => $length_start,
                            'column' => [
                                'start' => $column[$line] - $length_start - mb_strlen($explode_tag[0]),
                                'end' => $column[$line] - mb_strlen($explode_tag[0])
                            ]
                        ];
                        if(empty($tag_list[$line])){
                            $tag_list[$line] = [];
                        }
                        $tag_list[$line][] = $record;
                    }
                }
                $text = '';
                $explode = explode("\n", $tag);
                $count = count($explode);
                if($count > 1){
                    $content = trim(mb_substr($tag, 2, -2));
                    $length_start = mb_strlen($explode[0]);
                    $record = [
                        'tag' => $tag,
                        'is_multiline' => true,
                        'line' => [
                            'start' => $line - $count + 1,
                            'end' => $line
                        ],
                        'length' => [
                            'start' => $length_start,
                            'end' => mb_strlen($explode[$count - 1])
                        ],
                        'column' => [
                            ($line - $count + 1) => [
                                'start' => $column[$line - $count + 1] - $length_start,
                                'end' => $column[$line - $count + 1]
                            ],
                            $line => [
                                'start' => $column[$line] - mb_strlen($explode[$count - 1]),
                                'end' => $column[$line]
                            ]
                        ]
                    ];
                    if(empty($tag_list[$line - $count + 1])){
                        $tag_list[$line - $count + 1] = [];
                    }
                    $tag_list[$line - $count + 1][] = $record;
                } else {
                    $length_start = mb_strlen($explode[0]);
                    $record = [
                        'tag' => $tag,
                        'line' => $line,
                        'length' => $length_start,
                        'column' => [
                            'start' => $column[$line] - $length_start,
                            'end' => $column[$line]
                        ]
                    ];
                    $content = trim(mb_substr($tag, 2, -2));
                    if(mb_strtoupper(mb_substr($content, 0, 5)) === 'RAX}}'){
                        $record['is_header'] = true;
                        $record['content'] = $content;
                    }
                    elseif(mb_strtoupper(mb_substr($content, 0, 5)) === 'RAX}}'){
                        $record['is_header'] = true;
                        $record['content'] = $content;
                    }
                    elseif(
                        mb_strtoupper($content) === 'LITERAL' ||
                        $is_literal === true
                    ){
                        $is_literal = true;
                        $record['is_literal'] = true;
                        $record['is_literal_start'] = true;
                    }
                    elseif(
                        mb_strtoupper($content) === '/LITERAL' ||
                        $is_literal === true
                    ){
                        $is_literal = false;
                        $record['is_literal'] = true;
                        $record['is_literal_end'] = true;
                    }
                    if(empty($tag_list[$line])){
                        $tag_list[$line] = [];
                    }
                    $tag_list[$line][] = $record;
                }
                $tag = false;
                $column[$line]--;
            }
            elseif(
                $tag &&
                $is_comment === false
            ){
                $tag .= $char;
            }
            elseif($is_comment === false){
                $text .= $char;
            }
            if($char !== "\n") {
                $column[$line]++;
            }
        }
        if($text !== ''){
            $explode = explode("\n", $text);
            $count = count($explode);
            $explode_tag = explode("\n", $tag);
            if($count > 1){
                $length_start = mb_strlen($explode[0]);
                $record = [
                    'text' => $text,
                    'is_multiline' => true,
                    'line' => [
                        'start' => $line - $count + 1,
                        'end' => $line
                    ],
                    'length' => [
                        'start' => $length_start,
                        'end' => mb_strlen($explode[$count - 1])
                    ],
                    'column' => [
                        ($line - $count + 1) => [
                            'start' => $column[$line - $count + 1] - $length_start,
                            'end' => $column[$line - $count + 1]
                        ],
                        $line => [
                            'start' => $column[$line] - mb_strlen($explode[$count - 1]) - mb_strlen($explode_tag[0]),
                            'end' => $column[$line] - mb_strlen($explode_tag[0])
                        ]
                    ]
                ];
                if(empty($tag_list[$line - $count + 1])){
                    $tag_list[$line - $count + 1] = [];
                }
                $tag_list[$line - $count + 1][] = $record;
            } else {
                $length_start = mb_strlen($explode[0]);
                $record = [
                    'text' => $text,
                    'line' => $line,
                    'length' => $length_start,
                    'column' => [
                        'start' => $column[$line] - $length_start - mb_strlen($explode_tag[0]),
                        'end' => $column[$line] - mb_strlen($explode_tag[0])
                    ]
                ];
                if(empty($tag_list[$line])){
                    $tag_list[$line] = [];
                }
                $tag_list[$line][] = $record;
            }            
        }        
        return $tag_list;
    }

    public static function remove(App $object, $flags, $options, $tags=[]): array
    {
        if(!is_array($tags)){
            return $tags;
        }
        foreach($tags as $line => $tag){
            foreach($tag as $nr => $record){
                if(
                    array_key_exists('tag', $record) &&
                    $record['tag'] === '{{'
                ){
                    unset($tags[$line][$nr]);
                    if(empty($tags[$line])){
                        unset($tags[$line]);
                    }
                }
                elseif(
                    array_key_exists('is_header', $record) ||
                    array_key_exists('is_literal', $record) &&
                    !array_key_exists('is_literal_start', $record) &&
                    !array_key_exists('is_literal_end', $record)
                ){
                    unset($tags[$line][$nr]);
                    if(empty($tags[$line])){
                        unset($tags[$line]);
                    }
                }
            }
        }
        return $tags;
    }

    public static function block_method(App $object, $flags, $options, $tags=[]): array
    {
        $block_functions = [
            'if',
            'block.',
            'script',
            'link',
            'foreach',
            'for.each',
            'for',
            'while',
            'switch'
        ];

        $block_depth = 0;
        $is_block = false;
        $method_name = false;
        $method = false;
        $block_function = false;
        $block_array = [];
        $block_if = [];
        $block_else_if = [];
        $block_else = [];
        $has_block_if = false;
        $is_else = false;
        $is_else_if = false;

        foreach($tags as $line => $tag){
            foreach($tag as $nr => $record){
                if(
                    $is_block === false &&
                    array_key_exists('method', $record)
                ){
                    $method = $record['method'];
                    $method_name = $method['name'];
                    foreach($block_functions as $block_function){
                        $block_length = mb_strlen($block_function);
                        if(mb_substr($method_name, 0, $block_length) === $block_function){
                            if($is_block === false){
                                $is_block = [
                                    $line => $nr
                                ];
                            }
                            $block_depth++;
                            break;
                        }
                    }
                    continue;
                }
                if($is_block !== false){
                    if(array_key_exists('method', $record)){
                        $record_method_name = $record['method']['name'];
                        if($record_method_name === $method_name){
                            $block_depth++;
                        }
                    }
                    if(array_key_exists('marker', $record)){
                        $marker_name = $record['marker']['name'];
                        if($marker_name === $method_name){
                            $block_depth--;
                            if($block_depth === 0){
                                d($method);
                                d($block_if);
                                d($block_else_if);
                                d($block_else);
                                d($nr);
                                d($line);
                                ddd($is_block);

                                $is_block = false;
                            }
                        }
                    }
                    if(
                        $method_name === 'if' &&
                        $block_depth === 1
                    ){
                        if(array_key_exists('method', $record)){
                            $record_method_name = $record['method']['name'];
                            if(
                                in_array(
                                    $record_method_name,
                                    [
                                        'else.if',
                                        'elseif',
                                    ],
                                    true
                                )
                            ){
                                $has_block_if = true;
                                $is_else_if = true;
                            }
                        }
                        if(array_key_exists('marker', $record)){
                            $record_marker_name = $record['marker']['name'];
                            if(
                                in_array(
                                    $record_marker_name,
                                    [
                                        'else'
                                    ],
                                    true
                                )
                            ){
                                $has_block_if = true;
                                $is_else = true;
                            }
                        }
                        if($has_block_if === false){
                            $block_if[] = $record;
                        }
                        elseif($is_else_if === true){
                            $block_else_if[] = $record;
                        }
                        elseif($is_else === true){
                            $block_else[] = $record;
                        }
                    }
                }
            }
        }
        return $tags;
    }
}