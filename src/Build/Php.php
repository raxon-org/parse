<?php
namespace Raxon\Parse\Build;

use Error;

use Exception;

use ParseError;

use Plugin\Format_code;

use Raxon\App;

use Raxon\Exception\LocateException;

use Raxon\Exception\TemplateException;
use Raxon\Module\Autoload;
use Raxon\Module\Cli;
use Raxon\Module\Core;
use Raxon\Module\File;

use Raxon\Parse\Module\Token;

use Raxon\Parse\Module\Validator;
use ReflectionClass;
use ReflectionMethod;
use ReflectionObject;

class Php {

    use Format_code;

    /**
     * @throws Exception
     */
    public static function document_default(App $object, $flags, $options): void
    {
        $use_class = $object->config('package.raxon/parse.build.use.class');
        if(empty($use_class)) {
            $use_class = [];
            $use_class[] = 'Error';
            $use_class[] = 'ErrorException';
            $use_class[] = 'Exception';
            $use_class[] = 'ParseError';
            $use_class[] = 'Plugin';
            $use_class[] = 'Raxon\App';
            $use_class[] = 'Raxon\Exception\TemplateException';
            $use_class[] = 'Raxon\Exception\LocateException';
            $use_class[] = 'Raxon\Module\Data';
            $use_class[] = 'Raxon\Parse\Module\Parse';
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
        $object->config('package.raxon/parse.build.run.throw', [
            'Error',
            'ErrorException',
            'Exception',
            'Raxon\Exception\TemplateException',
            'Raxon\Exception\LocateException',
            'ParseError',
        ]);
    }

    public static function document_tag_prepare(App $object, $flags, $options, $tags=[]): array
    {
        return $tags;
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
//        $document[] = 'set_error_handler(function($severity, $message, $file, $line){';
//        $document[] = str_repeat(' ', 4) . 'throw new ErrorException($message, 0, $severity, $file, $line);';
//        $document[] = '});';
        $document[] = '';
        return $document;
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
        $document[] = str_repeat(' ', $indent * 4) . '$this->parse_flags($flags);';
        $document[] = str_repeat(' ', $indent * 4) . '$this->parse_options($options);';
        $object->config(
            'package.raxon/parse.build.state.indent',
            $object->config('package.raxon/parse.build.state.indent') - 1
        );
        $indent = $object->config('package.raxon/parse.build.state.indent');
        $document[] = str_repeat(' ', $indent * 4) . '}';
        return $document;
    }

    public static function document_run_throw(App $object, $flags, $options, $document=[]): array
    {
        $indent = $object->config('package.raxon/parse.build.state.indent');
        $throws = $object->config('package.raxon/parse.build.run.throw');
        if(is_array($throws)){
            $document[] = str_repeat(' ', $indent * 4) . '/**';
            foreach($throws as $throw){
                $document[] = str_repeat(' ', $indent * 4) . ' * @throws ' . $throw;
            }
            $document[] = str_repeat(' ', $indent * 4) . ' */';
        }
        return $document;
    }

    public static function document_run(App $object, $flags, $options, $document = [], $data = []): array
    {
        $indent = $object->config('package.raxon/parse.build.state.indent');
        $document = Php::document_run_throw($object, $flags, $options, $document);
        $document[] = str_repeat(' ', $indent * 4) . 'public function run(): mixed';
        $document[] = str_repeat(' ', $indent * 4) . '{';
        $indent++;
        $document[] = str_repeat(' ', $indent * 4) . '$content = [];';
        $document[] = str_repeat(' ', $indent * 4) . '$object = $this->object();';
        $document[] = str_repeat(' ', $indent * 4) . '$parse = $this->parse();';
        $document[] = str_repeat(' ', $indent * 4) . '$data = $this->data();';
        $document[] = str_repeat(' ', $indent * 4) . '$flags = $this->parse_flags();';
        $document[] = str_repeat(' ', $indent * 4) . '$options = $this->parse_options();';
        $document[] = str_repeat(' ', $indent * 4) . '$options->debug = true;';
        $document[] = str_repeat(' ', $indent * 4) . 'if (!($object instanceof App)) {';
        $indent++;
        $document[] = str_repeat(' ', $indent * 4) . 'throw new TemplateException(\'$object is not an instance of Raxon\App\');';
        $indent--;
        $document[] = str_repeat(' ', $indent * 4) . '}';
        $document[] = str_repeat(' ', $indent * 4) . 'if (!($parse instanceof Parse)) {';
        $indent++;
        $document[] = str_repeat(' ', $indent * 4) . 'throw new TemplateException(\'$parse is not an instance of Package\Raxon\Parse\Module\Parse\');';
        $indent--;
        $document[] = str_repeat(' ', $indent * 4) . '}';
        $document[] = str_repeat(' ', $indent * 4) . 'if (!($data instanceof Data)) {';
        $indent++;
        $document[] = str_repeat(' ', $indent * 4) . 'throw new TemplateException(\'$data is not an instance of Raxon\Module\Data\');';
        $indent--;
        $document[] = str_repeat(' ', $indent * 4) . '}';
        $document[] = str_repeat(' ', $indent * 4) . 'if (!is_object($flags)) {';
        $indent++;
        $document[] = str_repeat(' ', $indent * 4) . 'throw new TemplateException(\'$flags is not an object\');';
        $indent--;
        $document[] = str_repeat(' ', $indent * 4) . '}';
        $document[] = str_repeat(' ', $indent * 4) . 'if (!is_object($options)) {';
        $indent++;
        $document[] = str_repeat(' ', $indent * 4) . 'throw new TemplateException(\'$options is not an object\');';
        $indent--;
        $document[] = str_repeat(' ', $indent * 4) . '}';
//        $document[] = str_repeat(' ', $indent * 4) . 'try {';
//        $indent++;
        $document = Php::format($document, $data, $indent);
        $document[] = str_repeat(' ', $indent * 4) . 'return implode(\'\', $content);';
        $indent--;
//        $document[] = str_repeat(' ', $indent * 4) . '} catch(Error | ErrorException | Exception | ParseError | LocateException | TemplateException $exception) {';
//        $indent++;
//        $document[] = str_repeat(' ', $indent * 4) . 'throw $exception;';
//        $indent--;
//        $document[] = str_repeat(' ', $indent * 4) . '}';
//        $indent--;
        $document[] = str_repeat(' ', $indent * 4) . '}';
        return $document;
    }

    public static function format($document=[], $data=[], $indent=2): array
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
        $code = (new Php)->format_code($data, $format_options);
        foreach($code as $nr => $line){
            $document[] = $line;
        }
        return $document;
    }

    /**
     * @throws Exception
     */
    public static function document_tag(App $object, $flags, $options, $tags = []): array
    {
        if(!property_exists($options, 'source') || empty($options->source)){
            throw new Exception('Document tag: source not found in options' . PHP_EOL);
        }
        $data = [];
        $if_depth = 0;
        $if_length = 0;
        $elseif_count = 0;
        $else = false;
        $if_method = 'if';
        $for_depth = 0;
        $foreach_depth = 0;
        $while_depth = 0;
        $script_depth = 0;
        $link_depth = 0;
        $block_depth = 0;
        $capture_depth = 0;
        $content = [];
        foreach ($tags as $row_nr => $list) {
            foreach ($list as $nr => &$record) {
                $object->config('package.raxon/parse.build.state.tag', $record);
                $remove_newline_next = $object->config('package.raxon/parse.build.state.remove_newline_next');
                if($remove_newline_next){
                    $object->config('delete', 'package.raxon/parse.build.state.remove_newline_next');
                }
                if(
                    array_key_exists('method', $record) &&
                    array_key_exists('name', $record['method'])
                ){
                    if(
                        $record['method']['name'] === 'if' &&
                        $for_depth === 0 &&
                        $foreach_depth === 0 &&
                        $while_depth === 0 &&
                        $script_depth === 0 &&
                        $link_depth === 0 &&
                        $capture_depth === 0 &&
                        $block_depth === 0
                    ){
                        $if_depth++;
                        if($if_depth === 1){
                            if(!array_key_exists($if_method, $content)){
                                $content[$if_method] = [];
                            }
                            if(!array_key_exists('statement', $content[$if_method])){
                                $content[$if_method]['statement'] = $record;
                                continue;
                            }
                        }
                        elseif($if_depth > 1) {
                            $is_continue = false;
                            switch($if_method){
                                case 'if' :
                                case 'else' :
                                    if(!array_key_exists('content', $content[$if_method])){
                                        $content[$if_method]['content'] = [];
                                    }
                                    if(!array_key_exists($row_nr, $content[$if_method]['content'])){
                                        $content[$if_method]['content'][$row_nr] = [];
                                    }
                                    $content[$if_method]['content'][$row_nr][] = $record;
                                    $is_continue = true;
                                    break;
                                case 'elseif':
                                    if(!array_key_exists('content', $content[$if_method][$elseif_count - 1])){
                                        $content[$if_method]['content'] = [];
                                    }
                                    if(!array_key_exists($row_nr, $content[$if_method][$elseif_count - 1]['content'])){
                                        $content[$if_method]['content'][$row_nr] = [];
                                    }
                                    $content[$if_method][$elseif_count - 1]['content'][$row_nr][] = $record;
                                    $is_continue = true;
                                    break;
                            }
                            if($is_continue){
                                continue;
                            }
                        }
                    }
                    elseif(
                        in_array(
                            $record['method']['name'],
                            [
                                'else.if',
                                'else_if',
                                'elseif'
                            ],
                            true
                        ) &&
                        $if_depth === 1 &&
                        $for_depth === 0 &&
                        $while_depth === 0 &&
                        $foreach_depth === 0 &&
                        $script_depth === 0 &&
                        $link_depth === 0 &&
                        $capture_depth === 0 &&
                        $block_depth === 0
                    ){
                        $if_method = 'elseif';
                        $elseif_count++;
                        if(!array_key_exists($if_method, $content)){
                            $content[$if_method] = [];
                        }
                        if(!array_key_exists($elseif_count - 1, $content[$if_method])){
                            $content[$if_method][$elseif_count - 1] = [];
                        }
                        if(!array_key_exists('statement', $content[$if_method])){
                            $content[$if_method][$elseif_count - 1]['statement'] = $record;
                            continue;
                        }
                    }
                    elseif(
                        $record['method']['name'] === 'for' &&
                        $if_depth === 0 &&
                        $while_depth === 0 &&
                        $foreach_depth === 0 &&
                        $script_depth === 0 &&
                        $link_depth === 0 &&
                        $capture_depth === 0 &&
                        $block_depth === 0
                    ){
                        $for_depth++;
                        $category = 'for';
                        if($for_depth === 1){
                            if(!array_key_exists($category, $content)){
                                $content[$category] = [];
                            }
                            if(!array_key_exists('statement', $content[$category])){
                                $content[$category]['statement'] = $record;
                                continue;
                            }
                        }
                        elseif($for_depth > 1){
                            if(!array_key_exists('content', $content[$category])){
                                $content[$category]['content'] = [];
                            }
                            if(!array_key_exists($row_nr, $content[$category]['content'])){
                                $content[$category]['content'][$row_nr] = [];
                            }
                            $content[$category]['content'][$row_nr][] = $record;
                            continue;
                        }
                    }
                    elseif(
                        in_array(
                            $record['method']['name'],
                            [
                                'foreach',
                                'for.each',
                                'for_each'
                            ],
                            true
                        ) &&
                        $if_depth === 0 &&
                        $for_depth === 0 &&
                        $while_depth === 0 &&
                        $script_depth === 0 &&
                        $link_depth === 0 &&
                        $capture_depth === 0 &&
                        $block_depth === 0
                    ){
                        $foreach_depth++;
                        $category = 'foreach';
                        if($foreach_depth === 1){
                            if(!array_key_exists($category, $content)){
                                $content[$category] = [];
                            }
                            if(!array_key_exists('statement', $content[$category])){
                                $content[$category]['statement'] = $record;
                                continue;
                            }
                        }
                        elseif($foreach_depth > 1){
                            if(!array_key_exists('content', $content[$category])){
                                $content[$category]['content'] = [];
                            }
                            if(!array_key_exists($row_nr, $content[$category]['content'])){
                                $content[$category]['content'][$row_nr] = [];
                            }
                            $content[$category]['content'][$row_nr][] = $record;
                            continue;
                        }
                    }
                    elseif(
                        $record['method']['name'] === 'while' &&
                        $if_depth === 0 &&
                        $for_depth === 0 &&
                        $foreach_depth === 0 &&
                        $script_depth === 0 &&
                        $link_depth === 0 &&
                        $capture_depth === 0 &&
                        $block_depth === 0
                    ){
                        $while_depth++;
                        $category = 'while';
                        if($while_depth === 1){
                            if(!array_key_exists($category, $content)){
                                $content[$category] = [];
                            }
                            if(!array_key_exists('statement', $content[$category])){
                                $content[$category]['statement'] = $record;
                                continue;
                            }
                        }
                        elseif($while_depth > 1){
                            if(!array_key_exists('content', $content[$category])){
                                $content[$category]['content'] = [];
                            }
                            if(!array_key_exists($row_nr, $content[$category]['content'])){
                                $content[$category]['content'][$row_nr] = [];
                            }
                            $content[$category]['content'][$row_nr][] = $record;
                            continue;
                        }
                    }
                    elseif(
                        $record['method']['name'] === 'script' &&
                        $if_depth === 0 &&
                        $for_depth === 0 &&
                        $foreach_depth === 0 &&
                        $while_depth === 0 &&
                        $link_depth === 0 &&
                        $capture_depth === 0 &&
                        $block_depth === 0
                    ){
                        $script_depth++;
                        $category = 'script';
                        if($script_depth === 1){
                            if(!array_key_exists($category, $content)){
                                $content[$category] = [];
                            }
                            if(!array_key_exists('statement', $content[$category])){
                                $content[$category]['statement'] = $record;
                                continue;
                            }
                        }
                        elseif($script_depth > 1){
                            if(!array_key_exists('content', $content[$category])){
                                $content[$category]['content'] = [];
                            }
                            if(!array_key_exists($row_nr, $content[$category]['content'])){
                                $content[$category]['content'][$row_nr] = [];
                            }
                            $content[$category]['content'][$row_nr][] = $record;
                            continue;
                        }
                    }
                    elseif(
                        $record['method']['name'] === 'link' &&
                        $if_depth === 0 &&
                        $for_depth === 0 &&
                        $foreach_depth === 0 &&
                        $while_depth === 0 &&
                        $script_depth === 0 &&
                        $capture_depth === 0 &&
                        $block_depth === 0
                    ){
                        $link_depth++;
                        $category = 'link';
                        if($link_depth === 1){
                            if(!array_key_exists($category, $content)){
                                $content[$category] = [];
                            }
                            if(!array_key_exists('statement', $content[$category])){
                                $content[$category]['statement'] = $record;
                                continue;
                            }
                        }
                        elseif($link_depth > 1){
                            if(!array_key_exists('content', $content[$category])){
                                $content[$category]['content'] = [];
                            }
                            if(!array_key_exists($row_nr, $content[$category]['content'])){
                                $content[$category]['content'][$row_nr] = [];
                            }
                            $content[$category]['content'][$row_nr][] = $record;
                            continue;
                        }
                    }
                    elseif(
                        in_array(
                            $record['method']['name'],
                            [
                                'capture.append',
                                'capture.prepend',
                                'capture'
                            ],
                            true
                        ) &&
                        $if_depth === 0 &&
                        $for_depth === 0 &&
                        $foreach_depth === 0 &&
                        $while_depth === 0 &&
                        $script_depth === 0 &&
                        $link_depth === 0 &&
                        $block_depth === 0
                    ){
                        $capture_depth++;
                        $category = 'capture';
                        if($capture_depth === 1){
                            if(!array_key_exists($category, $content)){
                                $content[$category] = [];
                            }
                            if(!array_key_exists('statement', $content[$category])){
                                $content[$category]['statement'] = $record;
                                continue;
                            }
                        }
                        elseif($capture_depth > 1){
                            if(!array_key_exists('content', $content[$category])){
                                $content[$category]['content'] = [];
                            }
                            if(!array_key_exists($row_nr, $content[$category]['content'])){
                                $content[$category]['content'][$row_nr] = [];
                            }
                            $content[$category]['content'][$row_nr][] = $record;
                            continue;
                        }
                    }
                    elseif(
                        str_starts_with($record['method']['name'],'block.') &&
                        $if_depth === 0 &&
                        $for_depth === 0 &&
                        $foreach_depth === 0 &&
                        $while_depth === 0 &&
                        $script_depth === 0 &&
                        $link_depth === 0 &&
                        $capture_depth === 0
                    ){
                        $block_depth++;
                        $category = 'block';
                        if($block_depth === 1){
                            if(!array_key_exists($category, $content)){
                                $content[$category] = [];
                            }
                            if(!array_key_exists('statement', $content[$category])){
                                $content[$category]['statement'] = $record;
                                continue;
                            }
                        }
                        elseif($block_depth > 1){
                            if(!array_key_exists('content', $content[$category])){
                                $content[$category]['content'] = [];
                            }
                            if(!array_key_exists($row_nr, $content[$category]['content'])){
                                $content[$category]['content'][$row_nr] = [];
                            }
                            $content[$category]['content'][$row_nr][] = $record;
                            continue;
                        }
                    }
                    $record['if_depth'] = $if_depth;
                    $record['for_depth'] = $for_depth;
                    $record['foreach_depth'] = $foreach_depth;
                    $record['while_depth'] = $while_depth;
                    $record['script_depth'] = $script_depth;
                    $record['link_depth'] = $link_depth;
                    $record['block_depth'] = $block_depth;
                    $record['capture_depth'] = $capture_depth;
                }
                elseif(
                    array_key_exists('marker', $record) &&
                    array_key_exists('name', $record['marker'])
                ){
                    $record['if_depth'] = $if_depth;
                    $record['for_depth'] = $for_depth;
                    $record['foreach_depth'] = $foreach_depth;
                    $record['while_depth'] = $while_depth;
                    $record['script_depth'] = $script_depth;
                    $record['link_depth'] = $link_depth;
                    $record['block_depth'] = $block_depth;
                    $record['capture_depth'] = $capture_depth;
                    $marker_name = mb_strtolower($record['marker']['name']);
                    if($marker_name === 'rax'){
                        $object->config('package.raxon/parse.build.state.remove_newline_next', true);
                        continue;
                    }
                    elseif($marker_name === 'raw'){
                        $marker_data = [];
                        $marker_data[$record['line']] = [];
                        $marker_data[$record['line']][] = $record['marker']['value']['array'][2] ?? [];
                        $marker_data = Php::document_tag($object, $flags, $options, $marker_data);
                        foreach($marker_data as $line){
                            $data[] = $line;
                        }
                        $object->config('package.raxon/parse.build.state.is_raw', true);
                        continue;
//                        $method = Php::method($object, $flags, $options, $marker_data, $before, $after) . ';';
//                        $data[] = $method;
                    }
                    elseif($marker_name === 'else'){
                        if($if_depth === 1) {
                            $if_method = 'else';
                            continue;
                        }
                    }
                    elseif(
                        $marker_name === 'if' &&
                        array_key_exists('is_close', $record['marker']) &&
                        $record['marker']['is_close'] === true &&
                        $for_depth === 0 &&
                        $while_depth === 0 &&
                        $foreach_depth === 0 &&
                        $script_depth === 0 &&
                        $link_depth === 0 &&
                        $capture_depth === 0 &&
                        $block_depth === 0
                    ){
                        if($if_depth === 1){
                            $if_before = [];
                            $if_after = [];
                            $if_data = [];
                            $if_data[] = Php::method($object, $flags, $options, $content['if']['statement'], $before, $after) . '{';
                            if(!empty($before)){
                                foreach($before as $line){
                                    $if_before[] = $line;
                                }
                                $before = [];
                            }
                            if(!empty($after)){
                                foreach($after as $line){
                                    $if_after[] = $line;
                                }
                                $before = [];
                            }
                            $object->config('package.raxon/parse.build.state.remove_newline_next', true);
                            $if_content = PHP::document_tag($object, $flags, $options, $content['if']['content']);
                            foreach($if_content as $line){
                                $if_data[] = $line;
                            }
                            $if_data[] = '}';
                            if(array_key_exists('elseif', $content)){
                                foreach($content['elseif'] as $elseif_nr => $elseif){
                                    if(array_key_exists('statement', $elseif)){
                                        $if_data[] = Php::method($object, $flags, $options, $elseif['statement'], $before, $after) . '{';
                                        if(!empty($before)){
                                            foreach($before as $line){
                                                $if_before[] = $line;
                                            }
                                            $before = [];
                                        }
                                        if(!empty($after)){
                                            foreach($after as $line){
                                                $if_after[] = $line;
                                            }
                                            $before = [];
                                        }
                                        $object->config('package.raxon/parse.build.state.remove_newline_next', true);
                                        $if_content = Php::document_tag($object, $flags, $options, $elseif['content']);
                                        foreach($if_content as $line){
                                            $if_data[] = $line;
                                        }
                                        $if_data[] = '}';
                                    }
                                }
                            }
                            if(array_key_exists('else', $content)){
                                $object->config('package.raxon/parse.build.state.remove_newline_next', true);
                                $if_content = Php::document_tag($object, $flags, $options, $content['else']['content']);
                                $if_data[] = 'else {';
                                foreach($if_content as $line){
                                    $if_data[] = $line;
                                }
                                $if_data[] = '}';
                            }
                            foreach($if_before as $line){
                                $data[] = $line;
                            }
                            foreach($if_data as $line){
                                $data[] = $line;
                            }
                            foreach($if_after as $line){
                                $data[] = $line;
                            }
                            $content[$if_method] = [];
                        } else {
                            if($if_method === 'elseif'){
                                if(!array_key_exists($row_nr, $content[$if_method][$elseif_count - 1]['content'])){
                                    $content[$if_method]['content'][$row_nr] = [];
                                }
                                $content[$if_method][$elseif_count - 1]['content'][$row_nr][] = $record;
                            } else {
                                if(!array_key_exists($row_nr, $content[$if_method]['content'])){
                                    $content[$if_method]['content'][$row_nr] = [];
                                }
                                $content[$if_method]['content'][$row_nr][] = $record;
                            }
                        }
                        $if_depth--;
                        continue;
                    }
                    elseif(
                        $marker_name === 'for' &&
                        array_key_exists('is_close', $record['marker']) &&
                        $record['marker']['is_close'] === true &&
                        $if_depth === 0 &&
                        $foreach_depth === 0 &&
                        $while_depth === 0 &&
                        $script_depth === 0 &&
                        $link_depth === 0 &&
                        $capture_depth === 0 &&
                        $block_depth === 0
                    ){
                        $category = 'for';
                        if($for_depth === 1){
                            $for_before = [];
                            $for_after = [];
                            $for_data = [];
                            $separator = $object->config('package.raxon/parse.build.state.separator');
                            $separator_uuid = Core::uuid();
                            $object->config('package.raxon/parse.build.state.separator', $separator_uuid);
                            if(!array_key_exists('statement', $content[$category])){
                                ddd($content);
                            }
                            $for_data[] = Php::method($object, $flags, $options, $content[$category]['statement'], $before, $after) . '{';
                            if($separator === null){
                                $object->config('delete', 'package.raxon/parse.build.state.separator');
                            } else {
                                $object->config('package.raxon/parse.build.state.separator', $separator);
                            }
                            if(!empty($before)){
                                foreach($before as $line){
                                    $for_before[] = $line;
                                }
                                $before = [];
                            }
                            if(!empty($after)){
                                foreach($after as $line){
                                    $for_after[] = $line;
                                }
                                $before = [];
                            }
                            $object->config('package.raxon/parse.build.state.remove_newline_next', true);
                            $for_content = PHP::document_tag($object, $flags, $options, $content[$category]['content']);
                            foreach($for_content as $line){
                                $for_data[] = $line;
                            }
                            $for_data[] = '}';

                            foreach($for_before as $line){
                                $data[] = $line;
                            }
                            foreach($for_data as $line){
                                $data[] = $line;
                            }
                            foreach($for_after as $line){
                                $data[] = $line;
                            }
                            $content[$category] = [];
                        } else {
                            if(!array_key_exists($row_nr, $content[$category]['content'])){
                                $content[$category]['content'][$row_nr] = [];
                            }
                            $content[$category]['content'][$row_nr][] = $record;
                        }
                        $for_depth--;
                        continue;
                    }
                    elseif(
                        in_array(
                            $marker_name,
                            [
                                'foreach',
                                'for.each',
                                'for_each',
                                'for'
                            ],
                            true
                        ) &&
                        array_key_exists('is_close', $record['marker']) &&
                        $record['marker']['is_close'] === true &&
                        $if_depth === 0 &&
                        $while_depth === 0 &&
                        $for_depth === 0 &&
                        $script_depth === 0 &&
                        $link_depth === 0 &&
                        $capture_depth === 0 &&
                        $block_depth === 0
                    ){
                        $category = 'foreach';
                        if($foreach_depth === 1){
                            $foreach_before = [];
                            $foreach_after = [];
                            $foreach_data = [];
                            $separator = $object->config('package.raxon/parse.build.state.separator');
                            $separator_uuid = Core::uuid();
                            $object->config('package.raxon/parse.build.state.separator', $separator_uuid);
                            if(!array_key_exists('statement', $content[$category])){
                                ddd($content);
                            }
                            $foreach_data[] = Php::method($object, $flags, $options, $content[$category]['statement'], $before, $after, $inline_before, $inline_after) . '{';
                            if($separator === null){
                                $object->config('delete', 'package.raxon/parse.build.state.separator');
                            } else {
                                $object->config('package.raxon/parse.build.state.separator', $separator);
                            }
                            if(!empty($before)){
                                foreach($before as $line){
                                    $foreach_before[] = str_replace($separator, ';', $line);
                                }
                                $before = [];
                            }
                            if(!empty($after)){
                                foreach($after as $line){
                                    $foreach_after[] = str_replace($separator, ';', $line);
                                }
                                $before = [];
                            }
                            $object->config('package.raxon/parse.build.state.remove_newline_next', true);
                            if(!empty($inline_before)){
                                foreach($inline_before as $line){
                                    $foreach_data[] = str_replace($separator, ';', $line);
                                }
                                $inline_before = [];
                            }
                            $foreach_content = PHP::document_tag($object, $flags, $options, $content[$category]['content']);
                            $separator = $object->config('package.raxon/parse.build.state.separator');
                            foreach($foreach_content as $line){
                                $foreach_data[] = str_replace($separator,';', $line);
                            }
                            if(!empty($inline_after)){
                                foreach($inline_after as $line){
                                    $foreach_data[] = str_replace($separator, ';', $line);
                                }
                                $inline_after = [];
                            }
                            $foreach_data[] = '}';
                            foreach($foreach_before as $line){
                                $data[] = $line;
                            }
                            foreach($foreach_data as $line){
                                $data[] = $line;
                            }
                            foreach($foreach_after as $line){
                                $data[] = $line;
                            }
                            $content[$category] = [];
                        } else {
                            if(!array_key_exists($row_nr, $content[$category]['content'])){
                                $content[$category]['content'][$row_nr] = [];
                            }
                            $content[$category]['content'][$row_nr][] = $record;
                        }
                        $foreach_depth--;
                        continue;
                    }
                    elseif(
                        $marker_name === 'while' &&
                        array_key_exists('is_close', $record['marker']) &&
                        $record['marker']['is_close'] === true &&
                        $if_depth === 0 &&
                        $foreach_depth === 0 &&
                        $for_depth === 0 &&
                        $script_depth === 0 &&
                        $link_depth === 0 &&
                        $capture_depth === 0 &&
                        $block_depth === 0
                    ){
                        $category = 'while';
                        if($while_depth === 1){
                            $while_before = [];
                            $while_after = [];
                            $while_data = [];
                            $separator = $object->config('package.raxon/parse.build.state.separator');
                            $separator_uuid = Core::uuid();
                            $object->config('package.raxon/parse.build.state.separator', $separator_uuid);
                            if(!array_key_exists('statement', $content[$category])){
                                ddd($content);
                            }
                            $while_data[] = Php::method($object, $flags, $options, $content[$category]['statement'], $before, $after) . '{';
                            if($separator === null){
                                $object->config('delete', 'package.raxon/parse.build.state.separator');
                            } else {
                                $object->config('package.raxon/parse.build.state.separator', $separator);
                            }
                            if(!empty($before)){
                                foreach($before as $line){
                                    $while_before[] = $line;
                                }
                                $before = [];
                            }
                            if(!empty($after)){
                                foreach($after as $line){
                                    $while_after[] = $line;
                                }
                                $before = [];
                            }
                            $object->config('package.raxon/parse.build.state.remove_newline_next', true);
                            $while_content = PHP::document_tag($object, $flags, $options, $content[$category]['content']);
                            foreach($while_content as $line){
                                $while_data[] = $line;
                            }
                            $while_data[] = '}';

                            foreach($while_before as $line){
                                $data[] = $line;
                            }
                            foreach($while_data as $line){
                                $data[] = $line;
                            }
                            foreach($while_after as $line){
                                $data[] = $line;
                            }
                            $content[$category] = [];
                        } else {
                            if(!array_key_exists($row_nr, $content[$category]['content'])){
                                $content[$category]['content'][$row_nr] = [];
                            }
                            $content[$category]['content'][$row_nr][] = $record;
                        }
                        $while_depth--;
                        continue;
                    }
                    elseif(
                        $marker_name === 'script' &&
                        array_key_exists('is_close', $record['marker']) &&
                        $record['marker']['is_close'] === true &&
                        $if_depth === 0 &&
                        $foreach_depth === 0 &&
                        $for_depth === 0 &&
                        $while_depth === 0 &&
                        $link_depth === 0 &&
                        $capture_depth === 0 &&
                        $block_depth === 0
                    ){
                        $category = 'script';
                        if($script_depth === 1){
                            $block_before = [];
                            $block_after = [];
                            $block_data = [];
                            $separator = $object->config('package.raxon/parse.build.state.separator');
                            $separator_uuid = Core::uuid();
                            $object->config('package.raxon/parse.build.state.separator', $separator_uuid);
                            if(!array_key_exists('statement', $content[$category])){
                                ddd($content);
                            }
                            $variable_old = $options->variable ?? null;
                            $options->variable = Core::uuid_variable();
                            $object->config('package.raxon/parse.build.state.remove_newline_next', true);
                            $block_content = PHP::document_tag($object, $flags, $options, $content[$category]['content']);
                            $variable_argument = [
                                'type' => 'variable',
                                'tag' => $options->variable,
                                'name' => substr($options->variable, 1),
                                'is_reference' => false,
                                'is_not' => null,
                                'array_notation' => []
                            ];
                            $arguments = [];
                            $arguments[0] = [
                                'string' => $options->variable,
                                'array' => [ $variable_argument ]
                            ];
                            if(
                                array_key_exists('method', $content[$category]['statement']) &&
                                array_key_exists('argument', $content[$category]['statement']['method'])
                            ){
                                foreach($content[$category]['statement']['method']['argument'] as $argument){
                                    $arguments[] = $argument;
                                }
                            }
                            if(array_key_exists('method', $content[$category]['statement'])){
                                $content[$category]['statement']['method']['argument'] = $arguments;
                            }
                            $block_content[] = '$data->set(\'' . substr($options->variable, 1) . '\', implode(\'\', ' . $options->variable . '));';
                            $block_before[] = $options->variable . ' = [];';
                            $block_after[] = '$data->delete(\'' . substr($options->variable, 1) . '\');';
                            foreach($block_content as $line){
                                $block_before[] = $line;
                            }
                            if($variable_old){
                                $options->variable = $variable_old;
                            } else {
                                unset($options->variable);
                            }
                            $block_data[] = Php::method($object, $flags, $options, $content[$category]['statement'], $before, $after) . ';';
                            if($separator === null){
                                $object->config('delete', 'package.raxon/parse.build.state.separator');
                            } else {
                                $object->config('package.raxon/parse.build.state.separator', $separator);
                            }
                            if(!empty($before)){
                                foreach($before as $line){
                                    $block_before[] = $line;
                                }
                                $before = [];
                            }
                            if(!empty($after)){
                                foreach($after as $line){
                                    $block_after[] = $line;
                                }
                                $before = [];
                            }
                            foreach($block_before as $line){
                                $data[] = $line;
                            }
                            foreach($block_data as $line){
                                $data[] = $line;
                            }
                            foreach($block_after as $line){
                                $data[] = $line;
                            }
                            $content[$category] = [];
                        } else {
                            if(!array_key_exists($row_nr, $content[$category]['content'])){
                                $content[$category]['content'][$row_nr] = [];
                            }
                            $content[$category]['content'][$row_nr][] = $record;
                        }
                        $script_depth--;
                        continue;
                    }
                    elseif(
                        $marker_name === 'link' &&
                        array_key_exists('is_close', $record['marker']) &&
                        $record['marker']['is_close'] === true &&
                        $if_depth === 0 &&
                        $foreach_depth === 0 &&
                        $for_depth === 0 &&
                        $while_depth === 0 &&
                        $script_depth === 0 &&
                        $capture_depth === 0 &&
                        $block_depth === 0
                    ){
                        $category = 'link';
                        if($link_depth === 1){
                            $block_before = [];
                            $block_after = [];
                            $block_data = [];
                            $separator = $object->config('package.raxon/parse.build.state.separator');
                            $separator_uuid = Core::uuid();
                            $object->config('package.raxon/parse.build.state.separator', $separator_uuid);
                            if(!array_key_exists('statement', $content[$category])){
                                ddd($content);
                            }
                            $variable_old = $options->variable ?? null;
                            $options->variable = Core::uuid_variable();
                            $object->config('package.raxon/parse.build.state.remove_newline_next', true);
                            $block_content = PHP::document_tag($object, $flags, $options, $content[$category]['content']);
                            $variable_argument = [
                                'type' => 'variable',
                                'tag' => $options->variable,
                                'name' => substr($options->variable, 1),
                                'is_reference' => false,
                                'is_not' => null,
                                'array_notation' => []
                            ];
                            $arguments = [];
                            $arguments[0] = [
                                'string' => $options->variable,
                                'array' => [ $variable_argument ]
                            ];
                            if(
                                array_key_exists('method', $content[$category]['statement']) &&
                                array_key_exists('argument', $content[$category]['statement']['method'])
                            ){
                                foreach($content[$category]['statement']['method']['argument'] as $argument){
                                    $arguments[] = $argument;
                                }
                            }
                            if(array_key_exists('method', $content[$category]['statement'])){
                                $content[$category]['statement']['method']['argument'] = $arguments;
                            }
                            $block_content[] = '$data->set(\'' . substr($options->variable, 1) . '\', implode(\'\', ' . $options->variable . '));';
                            $block_before[] = $options->variable . ' = [];';
                            $block_after[] = '$data->delete(\'' . substr($options->variable, 1) . '\');';
                            foreach($block_content as $line){
                                $block_before[] = $line;
                            }
                            if($variable_old){
                                $options->variable = $variable_old;
                            } else {
                                unset($options->variable);
                            }
                            $block_data[] = Php::method($object, $flags, $options, $content[$category]['statement'], $before, $after) . ';';
                            if($separator === null){
                                $object->config('delete', 'package.raxon/parse.build.state.separator');
                            } else {
                                $object->config('package.raxon/parse.build.state.separator', $separator);
                            }
                            if(!empty($before)){
                                foreach($before as $line){
                                    $block_before[] = $line;
                                }
                                $before = [];
                            }
                            if(!empty($after)){
                                foreach($after as $line){
                                    $block_after[] = $line;
                                }
                                $before = [];
                            }
                            foreach($block_before as $line){
                                $data[] = $line;
                            }
                            foreach($block_data as $line){
                                $data[] = $line;
                            }
                            foreach($block_after as $line){
                                $data[] = $line;
                            }
                            $content[$category] = [];
                        } else {
                            if(!array_key_exists($row_nr, $content[$category]['content'])){
                                $content[$category]['content'][$row_nr] = [];
                            }
                            $content[$category]['content'][$row_nr][] = $record;
                        }
                        $link_depth--;
                        continue;
                    }
                    elseif(
                        $marker_name === 'block' &&
                        array_key_exists('is_close', $record['marker']) &&
                        $record['marker']['is_close'] === true &&
                        $if_depth === 0 &&
                        $foreach_depth === 0 &&
                        $for_depth === 0 &&
                        $while_depth === 0 &&
                        $script_depth === 0 &&
                        $link_depth === 0 &&
                        $capture_depth === 0
                    ){
                        $category = 'block';
                        if($block_depth === 1){
                            $block_before = [];
                            $block_after = [];
                            $block_data = [];
                            $separator = $object->config('package.raxon/parse.build.state.separator');
                            $separator_uuid = Core::uuid();
                            $object->config('package.raxon/parse.build.state.separator', $separator_uuid);
                            if(!array_key_exists('statement', $content[$category])){
                                ddd($content);
                            }
                            $variable_old = $options->variable ?? null;
                            $options->variable = Core::uuid_variable();
                            $object->config('package.raxon/parse.build.state.remove_newline_next', true);
                            $block_content = PHP::document_tag($object, $flags, $options, $content[$category]['content']);
                            $variable_argument = [
                                'type' => 'variable',
                                'tag' => $options->variable,
                                'name' => substr($options->variable, 1),
                                'is_reference' => false,
                                'is_not' => null,
                                'array_notation' => []
                            ];
                            $arguments = [];
                            $arguments[0] = [
                                'string' => $options->variable,
                                'array' => [ $variable_argument ]
                            ];
                            if(
                                array_key_exists('method', $content[$category]['statement']) &&
                                array_key_exists('argument', $content[$category]['statement']['method'])
                            ){
                                foreach($content[$category]['statement']['method']['argument'] as $argument){
                                    $arguments[] = $argument;
                                }
                            }
                            if(array_key_exists('method', $content[$category]['statement'])){
                                $content[$category]['statement']['method']['argument'] = $arguments;
                            }
                            $block_content[] = '$data->set(\'' . substr($options->variable, 1) . '\', implode(\'\', ' . $options->variable . '));';
                            $block_before[] = $options->variable . ' = [];';
                            $block_after[] = '$data->delete(\'' . substr($options->variable, 1) . '\');';
                            foreach($block_content as $line){
                                $block_before[] = $line;
                            }
                            if($variable_old){
                                $options->variable = $variable_old;
                            } else {
                                unset($options->variable);
                            }
                            if(property_exists($options, 'variable')){
                                $block_data[] = $options->variable . '[] = ' . Php::method($object, $flags, $options, $content[$category]['statement'], $before, $after) . ';';
                            } else {
                                $block_data[] = '$content[] = ' . Php::method($object, $flags, $options, $content[$category]['statement'], $before, $after) . ';';
                            }
                            if($separator === null){
                                $object->config('delete', 'package.raxon/parse.build.state.separator');
                            } else {
                                $object->config('package.raxon/parse.build.state.separator', $separator);
                            }
                            if(!empty($before)){
                                foreach($before as $line){
                                    $block_before[] = $line;
                                }
                                $before = [];
                            }
                            if(!empty($after)){
                                foreach($after as $line){
                                    $block_after[] = $line;
                                }
                                $before = [];
                            }
                            foreach($block_before as $line){
                                $data[] = $line;
                            }
                            foreach($block_data as $line){
                                $data[] = $line;
                            }
                            foreach($block_after as $line){
                                $data[] = $line;
                            }
                            $content[$category] = [];
                        } else {
                            if(!array_key_exists($row_nr, $content[$category]['content'])){
                                $content[$category]['content'][$row_nr] = [];
                            }
                            $content[$category]['content'][$row_nr][] = $record;
                        }
                        $block_depth--;
                        continue;
                    }
                    elseif(
                        in_array(
                            $marker_name,
                            [
                                'capture.append',
                                'capture.prepend',
                                'capture'
                            ],
                            true
                        ) &&
                        array_key_exists('is_close', $record['marker']) &&
                        $record['marker']['is_close'] === true &&
                        $if_depth === 0 &&
                        $foreach_depth === 0 &&
                        $for_depth === 0 &&
                        $while_depth === 0 &&
                        $script_depth === 0 &&
                        $link_depth === 0 &&
                        $block_depth === 0
                    ){
                        $category = 'capture';
                        if($capture_depth === 1){
                            $block_before = [];
                            $block_after = [];
                            $block_data = [];
                            $separator = $object->config('package.raxon/parse.build.state.separator');
                            $separator_uuid = Core::uuid();
                            $object->config('package.raxon/parse.build.state.separator', $separator_uuid);
                            if(!array_key_exists('statement', $content[$category])){
                                ddd($content);
                            }
                            $variable_old = $options->variable ?? null;
                            $options->variable = Core::uuid_variable();
                            $object->config('package.raxon/parse.build.state.remove_newline_next', true);
                            $block_content = PHP::document_tag($object, $flags, $options, $content[$category]['content']);
                            $variable_argument = [
                                'type' => 'variable',
                                'tag' => $options->variable,
                                'name' => substr($options->variable, 1),
                                'is_reference' => false,
                                'is_not' => null,
                                'array_notation' => []
                            ];
                            $arguments = [];
                            $arguments[0] = [
                                'string' => $options->variable,
                                'array' => [ $variable_argument ]
                            ];
                            if(
                                array_key_exists('method', $content[$category]['statement']) &&
                                array_key_exists('argument', $content[$category]['statement']['method'])
                            ){
                                foreach($content[$category]['statement']['method']['argument'] as $argument){
                                    $arguments[] = $argument;
                                }
                            }
                            if(array_key_exists('method', $content[$category]['statement'])){
                                $content[$category]['statement']['method']['argument'] = $arguments;
                            }
                            $block_content[] = '$data->set(\'' . substr($options->variable, 1) . '\', implode(\'\', ' . $options->variable . '));';
                            $block_before[] = $options->variable . ' = [];';
                            $block_after[] = '$data->delete(\'' . substr($options->variable, 1) . '\');';
                            foreach($block_content as $line){
                                $block_before[] = $line;
                            }
                            if($variable_old){
                                $options->variable = $variable_old;
                            } else {
                                unset($options->variable);
                            }
                            $block_data[] = Php::method($object, $flags, $options, $content[$category]['statement'], $before, $after) . ';';
                            if($separator === null){
                                $object->config('delete', 'package.raxon/parse.build.state.separator');
                            } else {
                                $object->config('package.raxon/parse.build.state.separator', $separator);
                            }
                            if(!empty($before)){
                                foreach($before as $line){
                                    $block_before[] = $line;
                                }
                                $before = [];
                            }
                            if(!empty($after)){
                                foreach($after as $line){
                                    $block_after[] = $line;
                                }
                                $before = [];
                            }
                            foreach($block_before as $line){
                                $data[] = $line;
                            }
                            foreach($block_data as $line){
                                $data[] = $line;
                            }
                            foreach($block_after as $line){
                                $data[] = $line;
                            }
                            $content[$category] = [];
                        } else {
                            if(!array_key_exists($row_nr, $content[$category]['content'])){
                                $content[$category]['content'][$row_nr] = [];
                            }
                            $content[$category]['content'][$row_nr][] = $record;
                        }
                        $capture_depth--;
                        continue;
                    }
                } else {
                    $record['if_depth'] = $if_depth;
                    $record['for_depth'] = $for_depth;
                    $record['foreach_depth'] = $foreach_depth;
                    $record['while_depth'] = $while_depth;
                    $record['script_depth'] = $script_depth;
                    $record['link_depth'] = $link_depth;
                    $record['block_depth'] = $block_depth;
                    $record['capture_depth'] = $capture_depth;
                }
                if($record['if_depth'] >= 1){
                    if(in_array($if_method, ['if', 'else'], true)){
                        if(!array_key_exists($if_method, $content)){
                            $content[$if_method] = [];
                        }
                        if(!array_key_exists('content', $content[$if_method])){
                            $content[$if_method]['content'] = [];
                        }
                        if(!array_key_exists($row_nr, $content[$if_method]['content'])){
                            $content[$if_method]['content'][$row_nr] = [];
                        }
                        $content[$if_method]['content'][$row_nr][] = $record;
                    }
                    elseif($if_method === 'elseif'){
                        if(!array_key_exists($if_method, $content)){
                            $content[$if_method] = [];
                        }
                        if(!array_key_exists($elseif_count - 1, $content[$if_method])){
                            $content[$if_method][$elseif_count - 1] = [];
                        }
                        if(!array_key_exists('content', $content[$if_method][$elseif_count - 1])){
                            $content[$if_method][$elseif_count - 1]['content'] = [];
                        }
                        if(!array_key_exists($row_nr, $content[$if_method][$elseif_count - 1]['content'])){
                            $content[$if_method][$elseif_count - 1]['content'][$row_nr] = [];
                        }
                        $content[$if_method][$elseif_count-1]['content'][$row_nr][] = $record;
                    }
                }
                elseif($record['for_depth'] >= 1){
                    if(!array_key_exists('for', $content)){
                        $content['for'] = [];
                    }
                    if(!array_key_exists('content', $content['for'])){
                        $content['for']['content'] = [];
                    }
                    if(!array_key_exists($row_nr, $content['for']['content'])){
                        $content['for']['content'][$row_nr] = [];
                    }
                    $content['for']['content'][$row_nr][] = $record;
                }
                elseif($record['foreach_depth'] >= 1){
                    if(!array_key_exists('foreach', $content)){
                        $content['foreach'] = [];
                    }
                    if(!array_key_exists('content', $content['foreach'])){
                        $content['foreach']['content'] = [];
                    }
                    if(!array_key_exists($row_nr, $content['foreach']['content'])){
                        $content['foreach']['content'][$row_nr] = [];
                    }
                    $content['foreach']['content'][$row_nr][] = $record;
                }
                elseif($record['while_depth'] >= 1){
                    if(!array_key_exists('while', $content)){
                        $content['while'] = [];
                    }
                    if(!array_key_exists('content', $content['while'])){
                        $content['while']['content'] = [];
                    }
                    if(!array_key_exists($row_nr, $content['while']['content'])){
                        $content['while']['content'][$row_nr] = [];
                    }
                    $content['while']['content'][$row_nr][] = $record;
                }
                elseif($record['script_depth'] >= 1){
                    if(!array_key_exists('script', $content)){
                        $content['script'] = [];
                    }
                    if(!array_key_exists('content', $content['script'])){
                        $content['script']['content'] = [];
                    }
                    if(!array_key_exists($row_nr, $content['script']['content'])){
                        $content['script']['content'][$row_nr] = [];
                    }
                    $content['script']['content'][$row_nr][] = $record;
                }
                elseif($record['link_depth'] >= 1){
                    if(!array_key_exists('link', $content)){
                        $content['link'] = [];
                    }
                    if(!array_key_exists('content', $content['link'])){
                        $content['link']['content'] = [];
                    }
                    if(!array_key_exists($row_nr, $content['link']['content'])){
                        $content['link']['content'][$row_nr] = [];
                    }
                    $content['link']['content'][$row_nr][] = $record;
                }
                elseif($record['block_depth'] >= 1){
                    if(!array_key_exists('block', $content)){
                        $content['block'] = [];
                    }
                    if(!array_key_exists('content', $content['block'])){
                        $content['block']['content'] = [];
                    }
                    if(!array_key_exists($row_nr, $content['block']['content'])){
                        $content['block']['content'][$row_nr] = [];
                    }
                    $content['block']['content'][$row_nr][] = $record;
                }
                elseif($record['capture_depth'] >= 1){
                    if(!array_key_exists('capture', $content)){
                        $content['capture'] = [];
                    }
                    if(!array_key_exists('content', $content['capture'])){
                        $content['capture']['content'] = [];
                    }
                    if(!array_key_exists($row_nr, $content['capture']['content'])){
                        $content['capture']['content'][$row_nr] = [];
                    }
                    $content['capture']['content'][$row_nr][] = $record;
                }
                else {
                    if(
                        array_key_exists('text', $record)
                    ){
                        if(empty($record['text'])){
                            continue;
                        }
                        if($remove_newline_next === true){
                            $record = Php::remove_newline_next($object, $flags, $options, $record);
                            $object->config('delete', 'package.raxon/parse.build.state.remove_newline_next');
                        }
                        if(
                            substr($record['text'], 0, 1) === '"' &&
                            substr($record['text'], -1) === '"'
                        ){
                            $variable_old = $options->variable ?? null;
                            $options->variable = Core::uuid_variable();
                            $data[] = $options->variable . ' = [];';
                            $single_quote_uuid = Core::uuid_variable();
                            $double_quote_uuid = Core::uuid_variable();
                            $ampersand_uuid = core::uuid_variable();
                            $text = $record['text'];
                            $text = str_replace('\\&', $ampersand_uuid, $text);
                            $text = str_replace('&quot;', $double_quote_uuid, $text);
                            $text = str_replace('&apos;', $single_quote_uuid, $text);
                            $token = Token::tokenize($object, $flags, $options, substr($text, 1, -1));
                            $token = Php::document_tag_prepare($object, $flags, $options, $token);
                            $embed = Php::document_tag($object, $flags, $options, $token);
                            $is_raw = $object->config('package.raxon/parse.build.state.is_raw');
//                            d($embed);
                            if(property_exists($options, 'variable')){
                                if($is_raw !== true){
                                    $data[] = $options->variable . '[] = \'"\';';
                                }
                                foreach($embed as $line){
                                    $line = str_replace($double_quote_uuid, '"', $line);
                                    $line = str_replace($single_quote_uuid, '\'', $line);
                                    $line = str_replace($ampersand_uuid, '&', $line);
                                    $data[] = $line;
                                }
                                if($is_raw !== true) {
                                    $data[] = $options->variable . '[] = \'"\';';
                                }
                            }
                            $object->config('delete', 'package.raxon/parse.build.state.is_raw');
                            if($variable_old){
                                $data[] = $variable_old . '[] = implode(\'\', ' . $options->variable . ');';
                                $options->variable = $variable_old;
                            } else {
                                $data[] = '$content[] = implode(\'\', ' . $options->variable . ');';
                                unset($options->variable);
                            }

                            /*
                            $uuid_variable = Core::uuid_variable();
                            $uuid_storage = Core::uuid_variable();
                            $uuid_parse = Core::uuid_variable();
                            $uuid_options = Core::uuid_variable();
                            $data[] = $uuid_options . ' = clone $options;';
                            $data[] = $uuid_options . '->source = \'internal_\' . Core::uuid();';
                            $data[] = $uuid_storage . '= new Data($data);';
                            $data[] = $uuid_parse . ' = new Parse($object, '. $uuid_storage . ', $flags, '. $uuid_options . ');';
                            $data[] = $uuid_variable . ' = '.  $uuid_parse . '->compile("' . substr($record['text'], 1, -1) . '", $data, true);';

                            if(property_exists($options, 'variable')){
                                $data[] = $options->variable . '[] = \'"\';';
                                $data[] = $options->variable . '[] = ' . $uuid_variable . ';';
                                $data[] = $options->variable . '[] = \'"\';';
                            } else {
                                $data[] = '$content[] = \'"\';';
                                $data[] = '$content[] = ' . $uuid_variable . ';';
                                $data[] = '$content[] = \'"\';';
                            }
                            */
                        }
                        elseif(
                            substr($record['text'], 0, 2) === '\\"' &&
                            substr($record['text'], -2) === '\\"'
                        ){
                            $variable_old = $options->variable ?? null;
                            $options->variable = Core::uuid_variable();
                            $data[] = $options->variable . ' = [];';
                            $text = str_replace('\\&', $ampersand_uuid, $text);
                            $text = str_replace('&quot;', $double_quote_uuid, $text);
                            $text = str_replace('&apos;', $single_quote_uuid, $text);
                            $token = Token::tokenize($object, $flags, $options, substr($record['text'], 2, -2));
                            $token = Php::document_tag_prepare($object, $flags, $options, $token);
                            $embed = Php::document_tag($object, $flags, $options, $token);
                            if(property_exists($options, 'variable')){
                                $data[] = $options->variable . '[] = \'\\"\';';
                                foreach($embed as $line){
                                    $line = str_replace($double_quote_uuid, '"', $line);
                                    $line = str_replace($single_quote_uuid, '\'', $line);
                                    $line = str_replace($ampersand_uuid, '&', $line);
                                    $data[] = $line;
                                }
                                $data[] = $options->variable . '[] = \'\\"\';';
                            }
                            if($variable_old){
                                $data[] = $variable_old . '[] = implode(\'\', ' . $options->variable . ');';
                                $options->variable = $variable_old;
                            } else {
                                $data[] = '$content[] = implode(\'\', ' . $options->variable . ');';
                                unset($options->variable);
                            }
                            /*
                            $uuid_variable = Core::uuid_variable();
                            $uuid_storage = Core::uuid_variable();
                            $uuid_parse = Core::uuid_variable();
                            $uuid_options = Core::uuid_variable();
                            $data[] = $uuid_options . ' = clone $options;';
                            $data[] = $uuid_options . '->source = \'internal_\' . Core::uuid();';
                            $data[] = $uuid_storage . '= new Data($data);';
                            $data[] = $uuid_parse . ' = new Parse($object, '. $uuid_storage . ', $flags, '. $uuid_options . ');';
                            $data[] = $uuid_variable . ' = '.  $uuid_parse . '->compile("' . substr($record['text'], 2, -2) . '", $data, true);';
                            if(property_exists($options, 'variable')){
                                $data[] = $options->variable . '[] = \'\\"\';';
                                $data[] = $options->variable . '[] = ' . $uuid_variable . ';';
                                $data[] = $options->variable . '[] = \'\\"\';';
                            } else {
                                $data[] = '$content[] = \'\\"\';';
                                $data[] = '$content[] = ' . $uuid_variable . ';';
                                $data[] = '$content[] = \'\\"\';';
                            }
                            */
                        }
                        else {
                            $text = Php::text($object, $flags, $options, $record);
                            if(property_exists($options, 'variable')){
                                $data[] = $options->variable . '[] =  "' . str_replace(['"'], ['\"'], $text) . '";';
                            } else {
                                $data[] = '$content[] =  "' . str_replace(['"'], ['\"'], $text) . '";';
//                                $data[] = '$content[] =  \'' . str_replace(['\\', '\''], ['\\\\', '\\\''], $text) . '\';';
                            }
                        }
                    }
                    elseif(array_key_exists('variable', $record)){
                        if(
                            array_key_exists('is_assign', $record['variable']) &&
                            $record['variable']['is_assign'] === true
                        ){
                            $variable = Php::variable_assign($object, $flags, $options, $record);
                            if($variable){
                                $data[] = $variable;
                            }
                            $next = $list[$nr + 1] ?? null;
                            if($next){
                                $list[$nr + 1] = Php::remove_newline_next($object, $flags, $options, $next);
                            }
                        }
                        elseif(
                            array_key_exists('is_define', $record['variable']) &&
                            $record['variable']['is_define'] === true
                        ){
                            $variable = Php::variable_define($object, $flags, $options, $record);
                            if($variable){
                                foreach($variable as $line){
                                    $data[] = $line;
                                }
                            }
                        }
                    }
                    elseif(
                        array_key_exists('method', $record)
                    ){
                        $method = Php::method($object, $flags, $options, $record, $before, $after);
                        if(!empty($before)){
                            $separator = $object->config('package.raxon/parse.build.state.separator');
                            $data[] = 'try {';
                            foreach($before as $line){
                                $data[] = str_replace($separator, ';', $line);
                            };
                            $before = [];
                            $data[] = '} catch (Error | ErrorException | Exception | ParseError | LocateException | TemplateException $exception) {';
                            if (
                                array_key_exists('is_multiline', $record) &&
                                $record['is_multiline'] === true
                            ) {
                                $data[] = 'throw new TemplateException(\'Method argument error (' .
                                    str_replace(['\\','\''], ['\\\\', '\\\''],  $record['tag']) .
                                    ')\' . PHP_EOL . (string) $exception . PHP_EOL . \'On line: ' .
                                    $record['line']['start'] .
                                    ', column: ' .
                                    $record['column'][$record['line']['start']]['start'] .
                                    ' in source: ' .
                                    $options->source .
                                    '.\');';
                            } else {
                                $data[] = 'throw new TemplateException(\'Method argument error (' .
                                    str_replace(['\\','\''], ['\\\\', '\\\''],  $record['tag']) .
                                    ')\' . PHP_EOL . (string) $exception . PHP_EOL . \'On line: ' .
                                    $record['line'] .
                                    ', column: ' .
                                    $record['column']['start'] .
                                    ' in source: ' .
                                    $options->source .
                                    '.\');';
                            }
                            $data[] = '}';
                        }
                        if($record['method']['name'] === 'break'){
                            $data[] = $method . ';';
                        } elseif($method) {
                            $uuid_method = Core::uuid_variable();
                            $data[] = 'try {';
                            $data[] = $uuid_method . ' = ' . $method . ';';
                            $data[] = '} catch (Error | ErrorException | Exception | ParseError | LocateException | TemplateException $exception) {';
                            if (
                                array_key_exists('is_multiline', $record) &&
                                $record['is_multiline'] === true
                            ) {
                                $data[] = 'throw new TemplateException(\'Method error (' .
                                    str_replace(['\\','\''], ['\\\\', '\\\''],  $record['tag']) .
                                    ')\' . PHP_EOL . (string) $exception . PHP_EOL . \'On line: ' .
                                    $record['line']['start'] .
                                    ', column: ' .
                                    $record['column'][$record['line']['start']]['start'] .
                                    ' in source: ' .
                                    $options->source .
                                    '.\');';
                            } else {
                                $data[] = 'throw new TemplateException(\'Method error (' .
                                    str_replace(['\\','\''], ['\\\\', '\\\''],  $record['tag']) .
                                    ')\' . PHP_EOL . (string) $exception . PHP_EOL . \'On line: ' .
                                    $record['line'] .
                                    ', column: ' .
                                    $record['column']['start'] .
                                    ' in source: ' .
                                    $options->source .
                                    '.\');';
                            }
                            $data[] = '}';
                            $data[] = 'if(is_scalar(' . $uuid_method . ')){';
                            if(property_exists($options, 'variable')){
                                $data[] = '    '. $options->variable . '[] = ' . $uuid_method . ';';
                            } else {
                                $data[] = '    $content[] = ' . $uuid_method . ';';
                            }
                            $data[] = '}';
                            $data[] = 'elseif(is_array(' . $uuid_method . ')){';
                            if($object->config('package.raxon/parse.build.state.source.is.json') === true){
                                $data[] = 'return ' . $uuid_method . ';';
                            } else {
                                if (
                                    array_key_exists('is_multiline', $record) &&
                                    $record['is_multiline'] === true
                                ) {
                                    $data[] = 'throw new TemplateException(\'Array to string conversion error (' .
                                        str_replace('\'', '\\\'', $record['tag']) .
                                        ')\' . PHP_EOL . \'On line: ' .
                                        $record['line']['start'] .
                                        ', column: ' .
                                        $record['column'][$record['line']['start']]['start'] .
                                        ' in source: ' .
                                        $options->source .
                                        '.\');';
                                } else {
                                    $data[] = 'throw new TemplateException(\'Array to string conversion error (' .
                                        str_replace('\'', '\\\'', $record['tag']) .
                                        ')\' . PHP_EOL . \'On line: ' .
                                        $record['line'] .
                                        ', column: ' .
                                        $record['column']['start'] .
                                        ' in source: ' .
                                        $options->source .
                                        '.\');';
                                }
                            }
                            $data[] = '}';
                            $data[] = 'elseif(is_object(' . $uuid_method . ')){';
                            if($object->config('package.raxon/parse.build.state.source.is.json') === true){
                                $data[] = 'return ' . $uuid_method . ';';
                            } else {
                                if (
                                    array_key_exists('is_multiline', $record) &&
                                    $record['is_multiline'] === true
                                ) {
                                    $data[] = 'throw new TemplateException(\'Object to string conversion error (' .
                                        str_replace('\'', '\\\'', $record['tag']) .
                                        ')\' . PHP_EOL . \'On line: ' .
                                        $record['line']['start'] .
                                        ', column: ' .
                                        $record['column'][$record['line']['start']]['start'] .
                                        ' in source: ' .
                                        $options->source .
                                        '.\');';
                                } else {
                                    $data[] = 'throw new TemplateException(\'Object to string conversion error (' .
                                        str_replace('\'', '\\\'', $record['tag']) .
                                        ')\' . PHP_EOL . \'On line: ' .
                                        $record['line'] .
                                        ', column: ' .
                                        $record['column']['start'] .
                                        ' in source: ' .
                                        $options->source .
                                        '.\');';
                                }
                            }
                            $data[] = '}';
                        }
                        if(!empty($after)){
                            foreach($after as $line){
                                $data[] = $line;
                            }
                            $after = [];
                        }
                        $next = $list[$nr + 1] ?? null;
                        if($next){
                            $list[$nr + 1] = Php::remove_newline_next($object, $flags, $options, $next);
                        }
                    }
                    else {
                        d($content);
                        ddd($record);
                    }
                }
            }
        }
        return $data;
    }

    /**
     * @throws Exception
     */
    public static function text(App $object, $flags, $options, $record = []): bool | string
    {
        $is_echo = $object->config('package.raxon/parse.build.state.echo');
        $ltrim = $object->config('package.raxon/parse.build.state.ltrim');
        $skip_space = $ltrim * 4;
        $skip = 0;
        if ($is_echo !== true) {
            return false;
        }
        if (
            array_key_exists('text', $record) &&
            $record['text'] !== ''
        ) {
            /*
            if(
                substr($record['text'], 0, 1) === '"' &&
                substr($record['text'], -1) === '"'
            ){
                return '" . $parse->compile("' . substr($record['text'], 1, -1) . '") . ";';
            }
            elseif(
                substr($record['text'], 0, 2) === '\\"' &&
                substr($record['text'], -2) === '\\"'
            ){
                return '" . $parse->compile("' . substr($record['text'], 1, -1) . '") . ";';
            }
            */
            //might need to remove // /* /**    **/  */
            $text = $record['text'];
            $ampersand_uuid = Core::uuid_variable();
            $double_quote_uuid = Core::uuid_variable();
            $single_quote_uuid = Core::uuid_variable();
            $text = str_replace('\\&', $ampersand_uuid, $text);
            $text = str_replace('&quot;', $double_quote_uuid, $text);
            $text = str_replace('&apos;', $single_quote_uuid, $text);
            $text = str_replace($ampersand_uuid, '&', $text);
            $text = str_replace($double_quote_uuid, '"', $text);
            $text = str_replace($single_quote_uuid, '\'', $text);
            return$text;
        }
        return false;
    }

    /**
     * @throws Exception
     */
    public static function method(App $object, $flags, $options, $record = [], &$before=[], &$after=[], &$inline_before=[], &$inline_after=[]): bool | string
    {
        $is_echo = $object->config('package.raxon/parse.build.state.echo');
        $ltrim = $object->config('package.raxon/parse.build.state.ltrim');
        $skip_space = $ltrim * 4;
        $skip = 0;
        if(
            array_key_exists('is_class_method', $record['method']) &&
            $record['method']['is_class_method'] === true
        ){
            $explode = explode(':', $record['method']['class']);
            if(array_key_exists(1, $explode)){
                $class = '\\' . implode('\\', $explode);
            } else {
                $class_static = Php::class_static($object);
                $class = $record['method']['class'];
                if(
                    !in_array(
                        $class,
                        $class_static,
                        true
                    )
                ) {
                    throw new Exception('Invalid class: ' . $class . ', available classes: ' . PHP_EOL . implode(PHP_EOL, $class_static));
                }
            }
            $method_value = $class .
                $record['method']['call_type'] .
                str_replace('.', '_', $record['method']['name']) .
                '(';
            $method_value .= Php::argument($object, $flags, $options, $record, $before, $after);
            $method_value .= ')';
            return $method_value;
        } else {
            if (
                in_array(
                    $record['method']['name'],
                    [
                        'if',
                        'elseif',
                        'else_if',
                        'else.if',
                        'for',
                        'foreach',
                        'for_each',
                        'for.each',
                        'break',
                        'while',
                    ],
                    true
                )
            ){
                if(
                    in_array(
                        $record['method']['name'],
                        [
                            'else_if',
                            'else.if'
                        ], true
                    )
                ){
                    $method_value = 'elseif(';
                }
                elseif($record['method']['name'] === 'break'){
                    $method_value = 'break';
                    if(
                        array_key_exists('argument', $record['method']) &&
                        array_key_exists(0, $record['method']['argument']) &&
                        array_key_exists('array', $record['method']['argument'][0]) &&
                        array_key_exists(0, $record['method']['argument'][0]['array']) &&
                        array_key_exists('type', $record['method']['argument'][0]['array'][0]) &&
                        $record['method']['argument'][0]['array'][0]['type'] === 'integer'
                    ){
                        $method_value .= ' ' . $record['method']['argument'][0]['array'][0]['value'];
                    }
                } else {
                    $method_value = $record['method']['name']  . '(';
                }
                if($record['method']['name'] === 'for'){
                    $method_value = [];
                    $is_argument = false;
                    $argument_count = count($record['method']['argument']);
                    $try_catch = $object->config('package.raxon/parse.build.state.try_catch');
                    $separator_uuid = Core::uuid();
                    $separator = $object->config('package.raxon/parse.build.state.separator');
                    $object->config('package.raxon/parse.build.state.separator', $separator_uuid);
                    $before_for = [];
                    $after_for = [];
                    if($argument_count === 3){
                        foreach($record['method']['argument'] as $nr => $argument){
                            if($nr > 0){
                                $object->config('package.raxon/parse.build.state.try_catch', false);
                            }
                            $value = Php::value($object, $flags, $options, $record, $argument, $is_set, $before_for, $after_for);
                            if(mb_strtolower($value) === 'null'){
                                $value = '';
                            }
                            $method_value[] = $value;
                        }
                        if($separator === null){
                            $object->config('delete', 'package.raxon/parse.build.state.separator');
                        } else {
                            $object->config('package.raxon/parse.build.state.separator', $separator);
                        }
                        $method_value[2] = str_replace($separator_uuid, ',', $method_value[2]);
                        $method_value[2] = substr($method_value[2], 0, -1);
                        $before[] = str_replace($separator_uuid, ';', $method_value[0]);
                        foreach($before_for as $line){
                            $before[] = str_replace($separator_uuid, ';', $line);
                        }
                        foreach($after_for as $line){
                            $after[] = str_replace($separator_uuid, ';', $line);
                        }
                        $method_value[0] = null;
                        $is_argument = true;
                    }
                    if($try_catch === null){
                        $object->config('delete', 'package.raxon/parse.build.state.try_catch');
                    } else {
                        $object->config('package.raxon/parse.build.state.try_catch', $try_catch);
                    }
                    if($is_argument === false) {
                        if (
                            array_key_exists('is_multiline', $record) &&
                            $record['is_multiline'] === true
                        ) {
                            throw new TemplateException(
                                $record['tag'] .
                                PHP_EOL .
                                'Invalid argument for {{for()}}' .
                                PHP_EOL .
                                'On line: ' .
                                $record['line']['start'] .
                                ', column: ' .
                                $record['column'][$record['line']['start']]['start'] .
                                ' in source: ' .
                                $options->source .
                                '.'
                            );
                        } else {
                            throw new TemplateException(
                                $record['tag'] .
                                PHP_EOL .
                                'Invalid argument for {{for()}}' .
                                PHP_EOL .
                                'On line: ' .
                                $record['line'] .
                                ', column: ' .
                                $record['column']['start'] .
                                ' in source: ' .
                                $options->source .
                                '.'
                            );
                        }
                    }
                    $method_value = 'for(' . implode(';', $method_value);
                    if($separator === null){
                        $object->config('delete', 'package.raxon/parse.build.state.separator');
                    } else {
                        $object->config('package.raxon/parse.build.state.separator', $separator);
                    }

                }
                elseif(
                    in_array(
                        $record['method']['name'],
                        [
                            'foreach',
                            'for_each',
                            'for.each'
                        ],
                        true
                    )
                ){
                    $method_value = [];
                    $is_argument = false;
                    $argument_count = count($record['method']['argument']);
                    $try_catch = $object->config('package.raxon/parse.build.state.try_catch');
                    $separator_uuid = Core::uuid();
                    $separator = $object->config('package.raxon/parse.build.state.separator');
                    $object->config('package.raxon/parse.build.state.separator', $separator_uuid);
                    $before_foreach = [];
                    $after_foreach = [];
                    if($argument_count === 1){
                        foreach($record['method']['argument'] as $nr => $argument){
//                            $object->config('package.raxon/parse.build.state.try_catch', false);
                            $argument_input = [];
                            $argument_input['string'] = $argument['array'][0]['tag'] ?? $argument['array'][0]['value'] ?? $argument['array'][0]['execute'] ?? null;
                            $argument_input['array'][] = $argument['array'][0];
                            $value = Php::value($object, $flags, $options, $record, $argument_input, $is_set, $before_foreach, $after_foreach);
                            if(
                                array_key_exists(2, $argument['array']) &&
                                array_key_exists(4, $argument['array'])
                            ){
                                $argument_input = [];
                                $argument_input['string'] = $argument['array'][2]['tag'] ?? $argument['array'][2]['value'] ?? $argument['array'][2]['execute'] ?? null;
                                $argument_input['array'][] = $argument['array'][2];
                                $foreach_value = Php::value($object, $flags, $options, $record, $argument_input, $is_set, $before_foreach_key, $after_foreach_key);
                                $explode = explode(' = $data->get(', substr($before_foreach_key[0], 0, -2));
//                                d($foreach_value);
//                                d($explode);
                                $inline_before[] = '$data->set(' . $explode[1] . ', ' . $explode[0] . ');';
                                $value .= ' as ' . $foreach_value;
                                $argument_input = [];
                                $argument_input['string'] = $argument['array'][4]['tag'] ?? $argument['array'][4]['value'] ?? $argument['array'][4]['execute'] ?? null;
                                $argument_input['array'][] = $argument['array'][4];
                                $foreach_value = Php::value($object, $flags, $options, $record, $argument_input, $is_set, $before_foreach_value, $after_foreach_value);
                                $explode = explode(' = $data->get(', substr($before_foreach_value[0], 0, -2));
                                $inline_before[] = '$data->set(' . $explode[1] . ', ' . $explode[0] . ');';
                                $value .= ' => ' . $foreach_value;
                            }
                            elseif(array_key_exists(2, $argument['array'])){
                                $argument_input = [];
                                $argument_input['string'] = $argument['array'][2]['tag'] ?? $argument['array'][2]['value'] ?? $argument['array'][2]['execute'] ?? null;
                                $argument_input['array'][] = $argument['array'][2];
                                $foreach_value = Php::value($object, $flags, $options, $record, $argument_input, $is_set, $before_foreach_value, $after_foreach_value);
                                $inline_before[] = str_replace($foreach_value . ' = $data->get(', '$data->set(', substr($before_foreach_value[0], 0, -2)) . ', ' . $foreach_value . ');';
                                $value .= ' as ' . $foreach_value;
                            }
                            if(mb_strtolower($value) === 'null'){
                                $value = '';
                            }
                            $method_value[] = $value;
                        }
                        if($separator === null){
                            $object->config('delete', 'package.raxon/parse.build.state.separator');
                        } else {
                            $object->config('package.raxon/parse.build.state.separator', $separator);
                        }
                        foreach($before_foreach as $line){
                            $before[] = str_replace($separator_uuid, ';', $line);
                        }
                        foreach($after_foreach as $line){
                            $after[] = str_replace($separator_uuid, ';', $line);
                        }
//                        $method_value[0] = null;
                        $is_argument = true;
                    }
                    if($try_catch === null){
                        $object->config('delete', 'package.raxon/parse.build.state.try_catch');
                    } else {
                        $object->config('package.raxon/parse.build.state.try_catch', $try_catch);
                    }
                    if($is_argument === false) {
                        if (
                            array_key_exists('is_multiline', $record) &&
                            $record['is_multiline'] === true
                        ) {
                            throw new TemplateException(
                                $record['tag'] .
                                PHP_EOL .
                                'Invalid argument for {{for.each()}}' .
                                PHP_EOL .
                                'On line: ' .
                                $record['line']['start'] .
                                ', column: ' .
                                $record['column'][$record['line']['start']]['start'] .
                                ' in source: ' .
                                $options->source .
                                '.'
                            );
                        } else {
                            throw new TemplateException(
                                $record['tag'] .
                                PHP_EOL .
                                'Invalid argument for {{for.each()}}' .
                                PHP_EOL .
                                'On line: ' .
                                $record['line'] .
                                ', column: ' .
                                $record['column']['start'] .
                                ' in source: ' .
                                $options->source .
                                '.'
                            );
                        }
                    }
                    if(array_key_exists(0, $method_value)
                    ){
                        $method_value = 'foreach(' . $method_value[0];
                    }
                    if($separator === null){
                        $object->config('delete', 'package.raxon/parse.build.state.separator');
                    } else {
                        $object->config('package.raxon/parse.build.state.separator', $separator);
                    }
                }
                elseif($record['method']['name'] === 'while'){
                    $method_value = [];
                    $is_argument = false;
                    $argument_count = count($record['method']['argument']);
                    $try_catch = $object->config('package.raxon/parse.build.state.try_catch');
                    $separator_uuid = Core::uuid();
                    $separator = $object->config('package.raxon/parse.build.state.separator');
                    $object->config('package.raxon/parse.build.state.separator', $separator_uuid);
                    $before_while = [];
                    $after_while = [];
                    if($argument_count === 1){
                        foreach($record['method']['argument'] as $nr => $argument){
                            $object->config('package.raxon/parse.build.state.try_catch', false);
                            $value = Php::value($object, $flags, $options, $record, $argument, $is_set, $before_while, $after_while);
                            d($before_while);
                            d($value);
                            if(mb_strtolower($value) === 'null'){
                                $value = '';
                            }
                            $method_value[] = $value;
                        }
                        if($separator === null){
                            $object->config('delete', 'package.raxon/parse.build.state.separator');
                        } else {
                            $object->config('package.raxon/parse.build.state.separator', $separator);
                        }
                        foreach($before_while as $line){
                            $before[] = str_replace($separator_uuid, ';', $line);
                        }
                        foreach($after_while as $line){
                            $after[] = str_replace($separator_uuid, ';', $line);
                        }
                        $is_argument = true;
                    } else {
                        if (
                            array_key_exists('is_multiline', $record) &&
                            $record['is_multiline'] === true
                        ) {
                            throw new TemplateException(
                                $record['tag'] .
                                PHP_EOL .
                                'Invalid argument count for {{while()}}, expected: 1, but got: ' . $argument_count .
                                PHP_EOL .
                                'On line: ' .
                                $record['line']['start'] .
                                ', column: ' .
                                $record['column'][$record['line']['start']]['start'] .
                                ' in source: ' .
                                $options->source .
                                '.'
                            );
                        } else {
                            throw new TemplateException(
                                $record['tag'] .
                                PHP_EOL .
                                'Invalid argument count for {{while()}}, expected: 1, but got: ' . $argument_count .
                                PHP_EOL .
                                'On line: ' .
                                $record['line'] .
                                ', column: ' .
                                $record['column']['start'] .
                                ' in source: ' .
                                $options->source .
                                '.'
                            );
                        }
                    }
                    if($try_catch === null){
                        $object->config('delete', 'package.raxon/parse.build.state.try_catch');
                    } else {
                        $object->config('package.raxon/parse.build.state.try_catch', $try_catch);
                    }
                    if($is_argument === false) {
                        if (
                            array_key_exists('is_multiline', $record) &&
                            $record['is_multiline'] === true
                        ) {
                            throw new TemplateException(
                                $record['tag'] .
                                PHP_EOL .
                                'Invalid argument for {{while()}}' .
                                PHP_EOL .
                                'On line: ' .
                                $record['line']['start'] .
                                ', column: ' .
                                $record['column'][$record['line']['start']]['start'] .
                                ' in source: ' .
                                $options->source .
                                '.'
                            );
                        } else {
                            throw new TemplateException(
                                $record['tag'] .
                                PHP_EOL .
                                'Invalid argument for {{while()}}' .
                                PHP_EOL .
                                'On line: ' .
                                $record['line'] .
                                ', column: ' .
                                $record['column']['start'] .
                                ' in source: ' .
                                $options->source .
                                '.'
                            );
                        }
                    }
                    $method_value = 'while(' . $method_value[0];
                    if($separator === null){
                        $object->config('delete', 'package.raxon/parse.build.state.separator');
                    } else {
                        $object->config('package.raxon/parse.build.state.separator', $separator);
                    }
                }
                elseif(
                    !in_array(
                        $record['method']['name'],
                        [
                            'break'
                        ],
                        true
                    )
                ) {
                    $method_value .= Php::argument($object, $flags, $options, $record, $before, $after);
                }
                if(
                    !in_array(
                        $record['method']['name'],
                        [
                            'break'
                        ],
                        true
                    )
                ) {
                    $method_value .= ')';
                }
                return $method_value;
            } else {
                $plugin = Php::plugin($object, $flags, $options, $record, str_replace('.', '_', $record['method']['name']));
                $method_value = $plugin . '(';
                $method_value .= Php::argument($object, $flags, $options, $record, $before, $after);
                $method_value .= ')';
                return $method_value;
            }
        }
        return false;
    }

    /**
     * @throws Exception
     */
    public static function class_static(App $object): array
    {
        $use_class = $object->config('package.raxon/parse.build.use.class');
        foreach($use_class as $use_class_nr => $use_class_record){
            $explode = explode('as', $use_class_record);
            if(array_key_exists(1, $explode)){
                $use_class[$use_class_nr] = trim($explode[1]);
            } else {
                $temp = explode('\\', $explode[0]);
                $use_class[$use_class_nr] = array_pop($temp);
            }
            $use_class[$use_class_nr] .= '::';
        }
        return $use_class;
    }

    /**
     * @throws Exception
     * @throws LocateException
     */
    public static function argument(App $object, $flags, $options, $record=[], &$before=[], &$after=[]): string
    {
        $is_argument = false;
        $argument_value = '';
        $previous_count = 0;
        $use_trait = $object->config('package.raxon/parse.build.use.trait');
        $use_trait_function = $object->config('package.raxon/parse.build.use.trait_function');
        $argument_is_reference = [];
        $argument_attribute = (object) [];
        $attributes = false;
        $attributes_transfer = false;
        if(
            array_key_exists('method', $record) &&
            array_key_exists('name', $record['method']) &&
            is_array($use_trait_function)
        ){
            $method_match = str_replace('.', '_', strtolower($record['method']['name']));
            $plugin_rename = $object->config('package.raxon/parse.plugin.rename');
            if(empty($plugin_rename)){
                throw new Exception('Invalid plugin rename configuration, please check your configuration file, try: app install raxon/parse -patch');
            }
            if(
                in_array(
                    $method_match,
                    $plugin_rename,
                    true
                )
            ){
                $method_match = 'plugin_' . $method_match;
            }
            $key = array_search($method_match, $use_trait_function, true);
            $trait = $use_trait[$key] ?? null;
            $trait_methods = [];
            try {
                $reflection = new ReflectionClass($trait);
                $trait_methods = $reflection->getMethods();
            }
            catch (Exception | Error | ParseError $exception) {
                throw $exception;
                //continue
            }
            foreach($trait_methods as $nr => $method){
                if(
                    strtolower($method->name) === $method_match
                ){
                    $attributes = $method->getAttributes();
                    foreach($attributes as $attribute_nr => $attribute){
                        $instance = $attribute->newInstance();
                        $instance->class = get_class($instance);
                        if($instance->class === 'Raxon\\Parse\\Attribute\\Argument'){
                            $argument_attribute = $instance;
                        }
                        $attributes[$attribute_nr] = $instance;
                    }
                    $parameters = $method->getParameters();
                    foreach($parameters as $parameter_nr => $parameter){
                        if($parameter->isPassedByReference()){
                            $argument_is_reference[$parameter_nr] = true;
                        } else {
                            $argument_is_reference[$parameter_nr] = false;
                        }
                    }
                }
            }
        }
        foreach($record['method']['argument'] as $nr => $argument) {
            if(
                array_key_exists('array', $argument) &&
                is_array($argument['array']) &&
                array_key_exists(0, $argument['array']) &&
                is_array($argument['array'][0]) &&
                array_key_exists('value', $argument['array'][0]) &&
                array_key_exists(1, $argument['array']) &&
                is_array($argument['array'][1]) &&
                array_key_exists('value', $argument['array'][1]) &&
                array_key_exists(2, $argument['array']) &&
                is_array($argument['array'][2]) &&
                array_key_exists('type', $argument['array'][2]) &&
                $argument['array'][2]['type'] === 'method'
            ) {
                $name = $argument['array'][0]['value'];
                $name .= $argument['array'][1]['value'];
                $class_static = Php::class_static($object);
                if(
                    in_array(
                        $name,
                        $class_static,
                        true
                    )
                ) {
                    $name .= $argument['array'][2]['method']['name'];
                    $argument = $argument['array'][2]['method']['argument'];
                    $use_trait = $object->config('package.raxon/parse.build.use.trait');
                    $trait = 'Plugin\\Validate';
                    if(
                        $attributes !== false &&
                        !in_array($trait, $use_trait, true)
                    ){
                        $attributes_transfer =  Core::object($attributes, Core::TRANSFER);
                        $use_trait[] = $trait;
                        $object->config('package.raxon/parse.build.use.trait', $use_trait);
                    }

                    foreach ($argument as $argument_nr => $argument_record) {
                        $value = Php::value($object, $flags, $options, $record, $argument_record, $is_set, $before,$after);
                        $uuid_variable = Core::uuid_variable();
                        $before[] = $uuid_variable . ' = ' . $value . ';';
                        if($attributes){
                            //need use_trait (config)
                            $before[] = '$this->validate(' . $uuid_variable . ', \'argument\', Core::object(\'' . $attributes_transfer . '\', Core::FINALIZE), ' . $argument_nr . ');';
                        }
                        $value = $uuid_variable;
                        $argument[$argument_nr] = $value;
                        /*
                        if(
                            array_key_exists($argument_nr, $argument_is_reference) &&
                            $argument_is_reference[$nr] === true
                        ){
                            $after[$nr] = '$data->set(\'' .  $after[$nr] . '\', ' . $uuid_variable . ');';
                        } else {
                            $after[$nr] = null;
                        }
                        */

                        $after[$argument_nr] = null;
                    }
                    ddd($before);
                }
                if (array_key_exists(0, $argument)) {
                    $argument = $name . '(' . implode(', ', $argument) . ')';
                } else {
                    $argument = $name . '()';
                }
            } else {
                if(
                    property_exists($argument_attribute, 'apply') &&
                    $argument_attribute->apply === 'literal' &&
                    property_exists($argument_attribute, 'count') &&
                    $argument_attribute->count === '*'
                ){
                    //all arguments are literal
                    $argument = '\'' . str_replace(['\\','\''], ['\\\\', '\\\''], trim($argument['string'])) . '\'';
                }
                elseif(
                    property_exists($argument_attribute, 'apply') &&
                    $argument_attribute->apply === 'literal' &&
                    property_exists($argument_attribute, 'index') &&
                    is_array($argument_attribute->index) &&
                    in_array(
                        $nr,
                        $argument_attribute->index,
                        true
                    )
                ){
                    //we have multiple indexes
                    $argument = '\'' . str_replace(['\\','\''], ['\\\\', '\\\''], trim($argument['string'])) . '\'';
                }
                elseif (
                    property_exists($argument_attribute, 'apply') &&
                    $argument_attribute->apply === 'literal' &&
                    property_exists($argument_attribute, 'index') &&
                    is_int($argument_attribute->index) &&
                    $argument_attribute->index === $nr
                ){
                    //we have a single index
                    $argument = '\'' . str_replace(['\\','\''], ['\\\\', '\\\''], trim($argument['string'])) . '\'';
                } else {
                    if(array_key_exists($nr, $argument_is_reference)){
                        $argument['array'][0]['is_reference'] = true;
                    }
                    $argument = Php::value($object, $flags, $options, $record, $argument, $is_set, $before, $after);
                    $uuid_variable = Core::uuid_variable();
                    $before[] = $uuid_variable . ' = ' . $argument . ';';
                    if($attributes !== false){
                        $use_trait = $object->config('package.raxon/parse.build.use.trait');
                        $trait = 'Plugin\\Validate';
                        if($attributes !== false && !in_array($trait, $use_trait, true)){
                            $use_trait[] = $trait;
                            $object->config('package.raxon/parse.build.use.trait', $use_trait);
//                            $attributes_transfer =  Core::object($attributes, Core::TRANSFER);
                        }
                        $attributes_transfer =  Core::object($attributes, Core::TRANSFER);
                        $before[] = '$this->validate(' . $uuid_variable . ', \'argument\', Core::object(\'' . $attributes_transfer . '\', Core::FINALIZE), ' . $nr . ');';
                    }
                    $argument = $uuid_variable;
                    /*
                    if(
                        array_key_exists($nr, $argument_is_reference) &&
                        $argument_is_reference[$nr] === true &&
                        array_key_exists($nr, $after) &&
                        array_key_exists('attribute', $after[$nr])
                    ){
                        $after[$nr] = '$data->set(\'' .  $after[$nr]['attribute'] . '\', ' . $uuid_variable . ');';
                    } else {
                        $after[$nr] = null;
                    }
                    */
                }
            }
            if($argument !== ''){
                $argument_value .= $argument  . ', ';
                $is_argument = true;
            }
        }
        if($is_argument){
            $argument_value = mb_substr($argument_value, 0, -2);
        }
        return $argument_value;
    }

    /**
     * @throws Exception
     * @throws LocateException
     */
    public static function plugin(App $object, $flags, $options, $record, $name): string
    {
        $source = $options->source ?? '';
        $name_lowercase = mb_strtolower($name);
        $plugin_rename = $object->config('package.raxon/parse.plugin.rename');
        if(empty($plugin_rename)){
            throw new Exception('Invalid plugin rename configuration, please check your configuration file, try: app install raxon/parse -patch');
        }
        if(
            in_array(
                $name_lowercase,
                $plugin_rename,
                true
            )
        ){
            $plugin = 'plugin_' . $name_lowercase;
        } else {
            $plugin = $name_lowercase;
        }
        $plugin = str_replace('.', '_', $plugin);
        $plugin = str_replace('-', '_', $plugin);
        $backslash_double = Core::uuid();
        $plugin = str_replace('\\\\', $backslash_double , $plugin);
        $plugin = str_replace('\\', '\\\\', $plugin);
        $plugin = str_replace($backslash_double, '\\\\', $plugin);
        $plugin = str_replace('\\\\', '_', $plugin);
        $use = $object->config('package.raxon/parse.build.use.trait');
        $use_trait_function = $object->config('package.raxon/parse.build.use.trait_function');
        if(!$use){
            $use = [];
        }
        if(!$use_trait_function){
            $use_trait_function = [];
        }
        if(str_contains($plugin, ':')){
            $explode = explode(':', $name, 2);
            $use_package = str_replace(
                    [
                        '_'
                    ],
                    [
                        '\\'
                    ], $explode[0]) .
                '\\'
            ;
            $explode = explode(':', $explode[1], 2);
            $use_plugin = null;
            if(array_key_exists(1, $explode)){
                $trait_name = $explode[0];
                $trait_function = $explode[1];
                $use_plugin = $trait_function;
            } else {
                d($use_package);
                d($plugin);
            }
            if(!in_array($use_plugin, $use_trait_function, true)){
                $use[] = '\\' . $use_package  . 'Trait' . '\\' . $trait_name;
                $use_trait_function[count($use) - 1] = $use_plugin;
                $object->config('package.raxon/parse.build.use.trait', $use);
                $object->config('package.raxon/parse.build.use.trait_function', $use_trait_function);
            }
            $object->config('package.raxon/parse.build.state.plugin.trait', '\\' . $use_package  . 'Trait' . '\\' . $trait_name);
            $object->config('package.raxon/parse.build.state.plugin.function', $use_plugin);
            return '$this->' . $use_plugin;
        } else {
            $is_code_point = false;
            $split = mb_str_split($name);
            $plugin_code_point = 'CodePoint_';
            foreach($split as $nr => $char){
                $ord = mb_ord($char);
                if($ord >= 256){
                    $is_code_point = true;
                    $plugin_code_point .= $ord . '_';
                }
            }
            if($is_code_point){
                $plugin = substr($plugin_code_point, 0, -1);
                if(strlen($plugin) > 64){
                    $plugin = 'hash_' . hash('sha256', $plugin);
                }
            }
            $use_plugin = explode('_', $plugin);
            foreach($use_plugin as $nr => $use_part){
                $use_plugin[$nr] = ucfirst($use_part);
            }
            $controller_plugin = implode('_', $use_plugin);
            $use_plugin = 'Plugin\\' . $controller_plugin;
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
                        'Plugin\\Value_Set',
                        'Plugin\\Framework',
                    ],
                    true
                )
            ){
                if(!in_array($use_plugin, $use, true)){
                    //pre scanning for the right exception
                    //this one breakpoint is wrong, it should not contain controller
                    $autoload = $object->data(App::AUTOLOAD_RAXON);
                    $autoload->addPrefix('Plugin', $object->config('controller.dir.plugin'));
                    $autoload->addPrefix('Plugin', $object->config('project.dir.plugin'));
                    $location = $autoload->locate($use_plugin, false,  Autoload::MODE_LOCATION);
                    $exist = false;
                    $locate_exception = [];
                    foreach($location  as $nr => $fileList){
                        foreach($fileList as $file){
                            $locate_exception[] = $file;
                            $exist = File::exist($file);
                            if($exist){
                                break 2;
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
                                str_replace(['\\','\''], ['\\\\', '\\\''], $record['tag']) .
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
                                str_replace(['\\','\''], ['\\\\', '\\\''], $record['tag']) .
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
                    $use_trait_function[count($use) - 1] = $plugin;
                }
            }
        }
        $object->config('package.raxon/parse.build.state.plugin.trait', $use_plugin);
        $object->config('package.raxon/parse.build.state.plugin.function', $plugin);
        $object->config('package.raxon/parse.build.use.trait', $use);
        $object->config('package.raxon/parse.build.use.trait_function', $use_trait_function);
        return '$this->' . mb_strtolower($plugin);
    }

    /**
     * @throws Exception
     * @throws LocateException
     */
    public static function value(App $object, $flags, $options, $tag, $input, &$is_set=false, &$before=[], &$after=[]): string
    {
        $source = $options->source ?? '';
        $value = '';
        $skip = 0;
        $input = Php::value_set($object, $flags, $options, $input, $is_set);
        foreach ($input['array'] as $nr => $record) {
            if($skip > 0){
                $skip--;
                continue;
            }
            if(array_key_exists('is_single_quoted', $record)){
                $remove_newline_next = $object->config('package.raxon/parse.build.state.remove_newline_next');
                if($remove_newline_next){
                    $record = Php::remove_newline_next($object, $flags, $options, $record);
                    $object->config('delete', 'package.raxon/parse.build.state.remove_newline_next');
                }
                $value .= $record['value'];
            }
            elseif(
                array_key_exists('type', $record) &&
                $record['type'] === 'integer'
            ){
                $value .= $record['execute'];
            }
            elseif(
                array_key_exists('type', $record) &&
                $record['type'] === 'variable'
            ){
                if(
                    array_key_exists('variable', $record) &&
                    array_key_exists('is_assign', $record['variable']) &&
                    $record['variable']['is_assign'] === true
                ){
                    $record['line'] = $tag['line'] ?? 'unknown';
                    $record['length'] = $tag['length'] ?? 'unknown';
                    $record['column'] = $tag['column'] ?? ['start' => 'unknown', 'end' => 'unknown'];
                    $value .= Php::variable_assign($object, $flags, $options, $record, $before, $after);
                    //remove next newline
                    $next = $input['array'][$nr + 1] ?? null;
                    if($next){
                        $input['array'][$nr + 1] = Php::remove_newline_next($object, $flags, $options, $next);

                    }
                } else {
                    $uuid_variable = Core::uuid_variable();
                    if(
                        array_key_exists('array_notation', $record) &&
                        !empty($record['array_notation'])
                    ){
                        $try_catch = $object->config('package.raxon/parse.build.state.try_catch');
                        $separator = $object->config('package.raxon/parse.build.state.separator');
                        $array_notation = Php::value($object, $flags, $options, $record, $record['array_notation'], $is_set, $before, $after);
                        $array_notation = explode('][', substr($array_notation, 1, -1));
                        $separator = $object->config('package.raxon/parse.build.state.separator');
                        if($try_catch === false){
                            $value .= '$this->value_child($data->get(\'' . $record['name'] . '\')' . ', ' . implode(', ', $array_notation) . ')';
                        } else {
                            $before[] = $uuid_variable . ' = $data->get(\'' . $record['name'] . '\');';
                            $before[] = $uuid_variable . ' = $this->value_child(' . $uuid_variable . ', ' . implode(', ', $array_notation) . ');';
                            $value .= $uuid_variable;
                        }
                    } else {
                        $try_catch = $object->config('package.raxon/parse.build.state.try_catch');
                        $separator = $object->config('package.raxon/parse.build.state.separator');
                        if ($try_catch === false) {
                            $value .= '$data->get(\'' . $record['name'] . '\')';
                        } else {
                            $before[] = $uuid_variable . ' = $data->get(\'' . $record['name'] . '\');';
                            $before[] = '$data->set(\'' . substr($uuid_variable, 1) . '\', ' . $uuid_variable . ');';
                            $value .= $uuid_variable;
                        }
                    }
                    if(
                        array_key_exists('is_reference', $record) &&
                        $record['is_reference'] === true
                    ){
                        $after[] = '$data->set(\'' . $record['name'] . '\', ' . $uuid_variable . ');';
                    }
                }
            }
            elseif(
                array_key_exists('value', $record) &&
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
                        'or',
                        'as',
                        '[',
                        ']',
                        ',',
                        '_',
                        '\'',
                        '"',
                        '(',
                        ')',
                        '!',
                        '=',
                    ],
                    true
                )
            ) {
                switch($record['value']){
                    case '??':
                    case '&&':
                    case 'and' :
                    case '||':
                    case 'or':
                    case 'xor':
                    case '=>':
                    case 'as':
                        $value .= ' ' . $record['value'] .  ' ';
                        break;
                    case '[':
                    case ']':
                    case ',':
                    case '_':
                    case '\'':
                    case '"':
                    case '(':
                    case ')':
                    case '!':
                    case '=':
                        $value .=  $record['value'];
                        break;
                    default:
                        $next = $input['array'][$nr + 1] ?? null;
                        $right = null;
                        if($next){
                            if(array_key_exists('is_single_quoted', $next)){
                                $right = $next['value'];
                            }
                            elseif(
                                array_key_exists('type', $next) &&
                                $next['type'] === 'variable'
                            ){
                                $uuid_variable = Core::uuid_variable();
                                $before[] = $uuid_variable . ' = $data->get(\'' . $next['name'] . '\');';
                                $before[] = '$data->set(\'' . substr($uuid_variable, 1) . '\', ' . $uuid_variable . ');';
                                $right = $uuid_variable;
                            }
                            elseif(
                                array_key_exists('type', $next) &&
                                $next['type'] === 'method'
                            ){
                                $uuid_variable = Core::uuid_variable();
//                                d($next);
                                $before[] = $uuid_variable . ' = ' . Php::method($object, $flags, $options, $next, $before, $after) . ';';
                                $right = $uuid_variable;
                            }
                            elseif(
                                array_key_exists('type', $next) &&
                                in_array(
                                    $next['type'],
                                    [
                                        'integer',
                                        'float',
                                    ],
                                    true
                                )
                            ){
                                $right = $next['execute'];
                            }
                            elseif(
                                array_key_exists('type', $next) &&
                                in_array(
                                    $next['type'],
                                    [
                                        'boolean',
                                    ],
                                    true
                                )
                            ){
                                $right = $next['value'];
                            }
                            elseif(
                                array_key_exists('type', $next) &&
                                in_array(
                                    $next['type'],
                                    [
                                        'string',
                                    ],
                                    true
                                )
                            ){
                                if(
                                    array_key_exists('is_raw', $next) &&
                                    $next['is_raw'] === true
                                ){
                                    $right = $next['value'];
                                } else {
                                    $right = $next['value'];
                                }
                            }
                            else {
                                ddd($next);
                            }
                            $skip++;
                            $value = Php::value_calculate($object, $flags, $options, $record['value'], $value, $right);
                        }
                        break;
                }
            }
            elseif(
                array_key_exists('type', $record) &&
                $record['type'] === 'method'
            ){
                if(empty($reord['tag'])){
                    $record['tag'] = $tag['tag'] ?? 'unknown';
                    $record['line'] = $tag['line'] ?? 'unknown';
                }
                $value .= Php::method($object, $flags, $options, $record, $before, $after);
            }
            elseif(
                array_key_exists('type', $record) &&
                $record['type'] === 'string'
            ){
                if(
                    array_key_exists('is_double_quoted', $record) &&
                    $record['is_double_quoted'] === true
                ){
                    $variable_old = $options->variable ?? null;
                    $options->variable = Core::uuid_variable();
                    $before[] = $options->variable . ' = [];';
                    $token = Token::tokenize($object, $flags, $options, $record['execute']);
                    $token = Php::document_tag_prepare($object, $flags, $options, $token);
                    $embed = Php::document_tag($object, $flags, $options, $token);
                    if(property_exists($options, 'variable')){
                        foreach($embed as $line){
                            $before[] = $line;
                        }
                    }
                    $before[] = $options->variable . ' = implode(\'\', ' . $options->variable . ');';
                    $value .= '"' . $options->variable . '"';
                    if($variable_old) {
                        $options->variable = $variable_old;
                    } else {
                        unset($options->variable);
                    }
                    //double_quoted_string needs to be simplified in the tokenizer als just a double quoted string instead of tokenized
                    /*
                    $uuid_variable = Core::uuid_variable();
                    $uuid_storage = Core::uuid_variable();
                    $uuid_parse = Core::uuid_variable();
                    $uuid_options = Core::uuid_variable();
                    d($record);


                    $before[] = $uuid_options . ' = clone $options;';
                    $before[] = $uuid_options . '->source = \'internal_\' . Core::uuid();';
                    $before[] = $uuid_storage . '= new Data($data);';
                    $before[] = $uuid_parse . ' = new Parse($object, '. $uuid_storage . ', $flags, '. $uuid_options . ');';
                    $before[] = $uuid_variable . ' = '.  $uuid_parse . '->compile("' . $record['execute'] . '", $data, true);';
//                    $before[] = $uuid_variable . ' = $parse->compile("' . $record['execute'] . '", $data, true);';
                    $value .= '"'. $uuid_variable . '"';
                    */
                }
                elseif(
                    array_key_exists('is_raw', $record) &&
                    $record['is_raw'] === true
                ) {
                    $value .= $record['value'];
                } else {
                    d('not implemented');
                    ddd($record);
                }
            }
            elseif(
                array_key_exists('type', $record) &&
                $record['type'] === 'boolean'
            ){
                $value .= $record['value'];
            }
            elseif(
                array_key_exists('type', $record) &&
                $record['type'] === 'array'
            ){
                $value .= Php::value($object, $flags, $options, $record, $record, $is_set_array, $before, $after);
                //remove next newline
                $object->config('package.raxon/parse.build.state.remove_newline_next', true);
            }
            elseif(array_key_exists('type', $record) &&
                $record['type'] === 'cast'
            ){
                $value .= $record['value'];
            }
            elseif(array_key_exists('type', $record) &&
                $record['type'] === 'whitespace'
            ){
                $value .= $record['value'];
            }
            else {
                d('not implemented');
                ddd($record);
            }
        }
        return $value;
    }

    public static function value_set(App $object, $flags, $options, $input, &$is_set=false): array
    {
//        d($input);
        if(!array_key_exists('array', $input)){
            return $input;
        }
        $count = count($input['array']);
        $first = reset($input['array']);
        if(!is_array($first)){
            trace();
            ddd($input);
        }
        if(
            $first !== false &&
            array_key_exists('value', $first) &&
            $first['value'] === '('
        ){
            $set = [];
            $set['type'] = 'set';
            $set['value'] = '(';
            $set['array'] = [];
            $set_depth = 1;
            $after = null;
            for($i = 1; $i <= $count - 1; $i++){
                $current = Token::item($input, $i);
                if($current === '('){
                    $set_depth++;
                }
                elseif($current === ')'){
                    $set_depth--;
                    if($set_depth === 0){
                        $after = [];
                    }
                }
                elseif($after !== null){
                    $after[] = $input['array'][$i];
                }
                elseif(
                    in_array(
                        $current,
                        [
                            'array',
                            'bool',
                            'boolean',
                            'int',
                            'integer',
                            'float',
                            'double',
                            'string',
                            'object',
                            'clone'
                        ],
                        true
                    )
                ){
                    $is_set = false;
                    return $input;
                } else {
                    $set['value'] .= $current;
                    $set['array'][] = $input['array'][$i];
                }
            }
            $set['value'] .= ')';
            if($after !== null){
                $input['array'] = [
                    0 => $set,
                ];
                foreach($after as $item){
                    $input['array'][] = $item;
                }
            } else {
                $input['array'] = [
                    0 => $set,
                ];
            }
            $is_set = true;
        }
        return $input;
    }

    public static function value_calculate(App $object, $flags, $options, $current, $left, $right): string
    {
        $value = '';
        switch($current){
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
     * @throws LocateException
     * @throws TemplateException
     */
    public static function remove_newline_next(App $object, $flags, $options, $record = []): array
    {
        if (
            array_key_exists('text', $record) &&
            array_key_exists('is_multiline', $record) &&
            $record['is_multiline'] === true
        ) {
            $data = mb_str_split($record['text']);
            $is_single_quote = false;
            $is_double_quote = false;
            $test = '';
            foreach ($data as $nr => $char) {
                if (
                    $char === '\'' &&
                    $is_double_quote === false &&
                    $is_single_quote === false
                ) {
                    $is_single_quote = true;
                } elseif (
                    $char === '\'' &&
                    $is_double_quote === false &&
                    $is_single_quote === true
                ) {
                    $is_single_quote = false;
                } elseif (
                    $char === '"' &&
                    $is_double_quote === false &&
                    $is_single_quote === false
                ) {
                    $is_double_quote = true;
                } elseif (
                    $char === '"' &&
                    $is_double_quote === true &&
                    $is_single_quote === false
                ) {
                    $is_double_quote = false;
                }
                if (
                    $char === "\n" &&
                    $is_single_quote === false &&
                    $is_double_quote === false
                ) {
                    $test = trim($test);
                    if ($test === '') {
                        $record['text'] = mb_substr($record['text'], $nr + 1);
                    }
                    break;
                }
                $test .= $char;
            }
        }
        return $record;
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
        $method_value = '';
        if(
            array_key_exists('method', $record['variable']) &&
            array_key_exists('operator', $record['variable']) &&
            array_key_exists('name', $record['variable']['method'])
        ){
            $method_value .= $record['variable']['operator'] . $record['variable']['method']['name'] . '(' . PHP_EOL;
            $is_argument = false;
            if(array_key_exists('argument', $record['variable']['method'])){
                foreach($record['variable']['method']['argument'] as $argument_nr => $argument){
                    $argument = Php::value($object, $flags, $options, $record, $argument, $is_set, $before, $after);
                    if($argument !== ''){
                        $method_value .= $argument . ',' . PHP_EOL;
                        $is_argument = true;
                    }
                }
                if($is_argument === true){
                    $method_value = mb_substr($method_value, 0, -2) . PHP_EOL . ')' . PHP_EOL;
                } else {
                    $method_value .= ')' . PHP_EOL;
                }
            }
        }
        if(array_key_exists('modifier', $record['variable'])){
            $before = [];
            $after = [];
            $previous_modifier = '$data->data(\'' . $variable_name . '\')' . $method_value;
            $modifier_value = $previous_modifier;
            foreach($record['variable']['modifier'] as $nr => $modifier){
                $plugin = Php::plugin($object, $flags, $options, $record, str_replace('.', '_', $modifier['name']));
                $modifier_value = $plugin . '(' . PHP_EOL;
                $modifier_value .= $previous_modifier . ',' . PHP_EOL;
                $is_argument = false;
                if(array_key_exists('argument', $modifier)){
                    foreach($modifier['argument'] as $argument_nr => $argument){
                        $argument = Php::value($object, $flags, $options, $record, $argument, $is_set, $before, $after);
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
            $is_not = '';
            if(array_key_exists('is_not', $record['variable'])){
                if($record['variable']['is_not'] === true){
                    $is_not = ' !! ';
                }
                elseif($record['variable']['is_not'] === false){
                    $is_not = ' !';
                }
            }
            if(
                array_key_exists('cast', $record['variable']) &&
                $record['variable']['cast'] !== false
            ){
                if($record['variable']['cast'] === 'clone'){
                    $value = 'clone ' . $value;
                } else {
                    $value = '(' . $record['variable']['cast'] . ') ' . $value;
                }
            }
            $separator = $object->config('package.raxon/parse.build.state.separator');
            $data = [];
            $data[] = 'try {';
            foreach($before as $line){
                $data[] = str_replace($separator, ';', $line);
            }
            $before = [];
            $data[] = $variable_uuid . ' = ' . $is_not . $value . ';';
            if(
                array_key_exists('is_multiline', $record) &&
                $record['is_multiline'] === true
            ){
                $data[] = 'if(' . $variable_uuid .' === null){';
//                $data[] = 'trace();';
//                $data[] = 'ddd($data);';
                $data[] = 'throw new TemplateException(\'Null-pointer exception: "$' . $variable_name . str_replace(['\\','\''], ['\\\\', '\\\''], $method_value) . '" on line: ' . $record['line']['start']  . ', column: ' . $record['column'][$record['line']['start']]['start'] . ' in source: '. $source . '.' . PHP_EOL . 'You can use modifier "default" to surpress it \');';
                $data[] = '}';
            } else {
                $data[] = 'if(' . $variable_uuid .' === null){';
//                $data[] = 'trace();';
//                $data[] = 'ddd($data);';
                $data[] = 'throw new TemplateException(\'Null-pointer exception: "$' . $variable_name . str_replace(['\\','\''], ['\\\\', '\\\''], $method_value) . '" on line: ' . $record['line']  . ', column: ' . $record['column']['start'] . ' in source: '. $source . '.'. PHP_EOL . 'You can use modifier "default" to surpress it \');';
                $data[] = '}';
            }
//            $data[] = 'd(' . $variable_uuid . ');';
            $data[] = 'elseif(is_scalar('. $variable_uuid. ')){';
            if(property_exists($options, 'variable')){
                $data[] = $options->variable . '[] = ' . $variable_uuid . ';';
            } else {
                $data[] = '$content[] = ' . $variable_uuid . ';';
            }
            $data[] = '}';
            $data[] = 'elseif(is_array(' . $variable_uuid. ')){';
            if (
                array_key_exists('is_multiline', $record) &&
                $record['is_multiline'] === true
            ){
                $data[] = 'throw new TemplateException(\'Array to string conversion exception: "$' . $variable_name . str_replace(['\\','\''], ['\\\\', '\\\''], $method_value) .'" on line: ' . $record['line']['start']  . ', column: ' . $record['column'][$record['line']['start']]['start'] . ' in source: '. $source . '.\');';
            } else {
                $data[] = 'throw new TemplateException(\'Array to string conversion exception: "$' . $variable_name . str_replace(['\\','\''], ['\\\\', '\\\''], $method_value) .'" on line: ' . $record['line']  . ', column: ' . $record['column']['start'] . ' in source: ' . $source . '.\');';
            }
            $data[] = '}';
            $data[] = 'elseif(is_object(' . $variable_uuid . ')){';
            if (
                array_key_exists('is_multiline', $record) &&
                $record['is_multiline'] === true
            ){
                $data[] = 'throw new TemplateException(\'Object to string conversion exception: "$' . $variable_name . str_replace(['\\','\''], ['\\\\', '\\\''], $method_value) .'" on line: ' . $record['line']['start']  . ', column: ' . $record['column'][$record['line']['start']]['start'] . ' in source: '. $source . '.\');';
            } else {
                $data[] = 'throw new TemplateException(\'Object to string conversion exception: "$' . $variable_name . str_replace(['\\','\''], ['\\\\', '\\\''], $method_value) .'" on line: ' . $record['line']  . ', column: ' . $record['column']['start'] . ' in source: ' . $source . '.\');';
            }
            $data[] = '}';
            foreach($after as $line){
                $data[] = $line;
            }
            $after = [];
            $data[] = '} catch (Error | ErrorException | Exception | ParseError | LocateException | TemplateException $exception) {'; //catch
            $data[] = 'throw $exception;';
            $data[] = '}';
            $object->config('package.raxon/parse.build.state.remove_newline_next', true);
            return $data;
        } else {
            $is_not = '';
            if(
                array_key_exists('is_not', $record['variable'])
            ){
                if($record['variable']['is_not'] === true){
                    $is_not = '!! ';
                }
                elseif($record['variable']['is_not'] === false){
                    $is_not = '! ';
                }
            }
            $cast = '';
            if(
                array_key_exists('cast', $record['variable']) &&
                $record['variable']['cast'] !== false
            ){
                if($record['variable']['cast'] === 'clone'){
                    $cast = 'clone ';
                } else {
                    $cast = '(' . $record['variable']['cast'] . ') ';
                }
            }
            if(array_key_exists('array_notation', $record['variable'])){
                $data = [];
                $before = [];
                $after = [];
                $data[] = 'try {';
                $array_notation = Php::value($object, $flags, $options, $record, $record['variable']['array_notation'], $is_set, $before, $after);
                $array_notation = explode('][', substr($array_notation, 1, -1));
                $root_uuid = Core::uuid_variable();
                $separator = $object->config('package.raxon/parse.build.state.separator');
                foreach($before as $line){
                    $data[] = str_replace($separator, ';', $line);
                }
                $before = [];
                $data[] = $root_uuid . ' = $data->get(\'' . $record['variable']['name'] . '\');';
                $data[] = $root_uuid . ' = $this->value_child(' . $root_uuid . ', ' . implode(', ', $array_notation) . ');';
                $data[] = $variable_uuid . ' = ' . $is_not . $cast . $root_uuid . ';';
                foreach($after as $line){
                    $data[] = $line;
                }
                $after = [];
                $data[] = '} catch (Error | ErrorException | Exception | ParseError | LocateException | TemplateException $exception) {';
                if(
                    array_key_exists('is_multiline', $record) &&
                    $record['is_multiline'] === true
                ) {
                    $data[] = 'throw new TemplateException(\'' . str_replace(['\\', '\''], ['\\\\', '\\\''], $record['tag']) . ' on line: ' . $record['line']['start'] . ', column: ' . $record['column'][$record['line']['start']]['start'] . ' in source: ' . $source . '.' . PHP_EOL . '\' . (string) $exception);';
                } else {
                    $data[] = 'throw new TemplateException(\'' . str_replace(['\\', '\''], ['\\\\', '\\\''], $record['tag']) . ' on line: ' . $record['line'] . ', column: ' . $record['column']['start'] . ' in source: ' . $source . '.' . PHP_EOL . '\' . (string) $exception);';
                }
                $data[] = '}';
            } else {
                $data = [];
                $data[] = 'try {';
                $data[] = $variable_uuid . ' = ' . $is_not . $cast . '$data->data(\'' . $variable_name . '\');' ;
                $data[] = '} catch (Error | ErrorException | Exception | ParseError | LocateException | TemplateException $exception) {';
                if(
                    array_key_exists('is_multiline', $record) &&
                    $record['is_multiline'] === true
                ) {
                    $data[] = 'throw new TemplateException(\'' . str_replace(['\\', '\''], ['\\\\', '\\\''], $record['tag']) . ' on line: ' . $record['line']['start'] . ', column: ' . $record['column'][$record['line']['start']]['start'] . ' in source: ' . $source . '.' . PHP_EOL . '\' . (string) $exception);';
                } else {
                    $data[] = 'throw new TemplateException(\'' . str_replace(['\\', '\''], ['\\\\', '\\\''], $record['tag']) . ' on line: ' . $record['line'] . ', column: ' . $record['column']['start'] . ' in source: ' . $source . '.' . PHP_EOL . '\' . (string) $exception);';
                }
                $data[] = '}';
            }
            if(
                array_key_exists('is_multiline', $record) &&
                $record['is_multiline'] === true
            ){
                $data[] = 'if(' . $variable_uuid .' === null){';
//                $data[] = 'trace();';
//                $data[] = 'ddd($data);';
                $data[] = 'throw new TemplateException(\'Null-pointer exception: "$' . $variable_name . str_replace(['\\','\''], ['\\\\', '\\\''], $method_value) .'" on line: ' . $record['line']['start']  . ', column: ' . $record['column'][$record['line']['start']]['start'] . ' in source: '. $source . '.' . PHP_EOL .  'You can use modifier "default" to surpress it \');';
                $data[] = '}';
            } else {
                $data[] = 'if(' . $variable_uuid .' === null){';
//                $data[] = 'trace();';
//                $data[] = 'ddd($data);';
                $data[] = 'throw new TemplateException(\'Null-pointer exception: "$' . $variable_name . str_replace(['\\','\''], ['\\\\', '\\\''], $method_value) .'" on line: ' . $record['line']  . ', column: ' . $record['column']['start'] . ' in source: ' . $source . '.' .  PHP_EOL . 'You can use modifier "default" to surpress it \');';
                $data[] = '}';
            }
            $data[] = 'elseif(is_scalar('. $variable_uuid. ')){';
            if(property_exists($options, 'variable')){
                $data[] = $options->variable . '[] = ' . $variable_uuid . ';';
            } else {
                $data[] = '$content[] =  ' . $variable_uuid . ';';
            }
            $data[] = '}';
            $data[] = 'elseif(is_array('. $variable_uuid. ')){';
            if (
                array_key_exists('is_multiline', $record) &&
                $record['is_multiline'] === true
            ){
                $data[] = 'throw new TemplateException(\'Array to string conversion exception: "$' . $variable_name . str_replace(['\\','\''], ['\\\\', '\\\''], $method_value) .'" on line: ' . $record['line']['start']  . ', column: ' . $record['column'][$record['line']['start']]['start'] . ' in source: '. $source . '.\');';
            } else {
                $data[] = 'throw new TemplateException(\'Array to string conversion exception: "$' . $variable_name . str_replace(['\\','\''], ['\\\\', '\\\''], $method_value) .'" on line: ' . $record['line']  . ', column: ' . $record['column']['start'] . ' in source: ' . $source . '.\');';
            }
            $data[] = '}';
            $data[] = 'elseif(is_object('. $variable_uuid. ')){';
            if (
                array_key_exists('is_multiline', $record) &&
                $record['is_multiline'] === true
            ){
                $data[] = 'throw new TemplateException(\'Object to string conversion exception: "$' . $variable_name . str_replace(['\\','\''], ['\\\\', '\\\''], $method_value) .'" on line: ' . $record['line']['start']  . ', column: ' . $record['column'][$record['line']['start']]['start'] . ' in source: '. $source . '.\');';
            } else {
                $data[] = 'throw new TemplateException(\'Object to string conversion exception: "$' . $variable_name . str_replace(['\\','\''], ['\\\\', '\\\''], $method_value) .'" on line: ' . $record['line']  . ', column: ' . $record['column']['start'] . ' in source: ' . $source . '.\');';
            }
            $data[] = '}';
            $object->config('package.raxon/parse.build.state.remove_newline_next', true);
            return $data;
        }
        return false;
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
        $before = [];
        $before_value = [];
        $after_value = [];
        $try_catch = $object->config('package.raxon/parse.build.state.try_catch');
        $separator = $object->config('package.raxon/parse.build.state.separator');
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
        }
        elseif(
            array_key_exists('value', $record['variable']) &&
            is_array($record['variable']['value']) &&
            array_key_exists('array', $record['variable']['value']) &&
            is_array($record['variable']['value']['array']) &&
            array_key_exists(0, $record['variable']['value']['array']) &&
            is_array($record['variable']['value']['array'][0]) &&
            array_key_exists('is_class_method', $record['variable']['value']['array'][0]) &&
            $record['variable']['value']['array'][0]['is_class_method'] === true
        ){
            //static class method call
//            breakpoint($record);
            $method = $record['variable']['value']['array'][0]['method']['name'] ?? null;
            $method = str_replace('.', '_', $method);
            $explode = explode('::', $method);
            $function = array_pop($explode);
            $method = implode('\\', $explode);
            if(array_key_exists(1, $explode) && $explode[0] !== ''){
                $method = '\\' . $method;
            }
            $class_name = $method;
            $method .= '::' . $function;
            $uuid = Core::uuid_variable();
            $uuid_methods = Core::uuid_variable();
            $argument = $record['variable']['value']['array'][0]['method']['argument'] ?? [];
            foreach($argument as $argument_nr => $argument_record){
                $value = Php::value($object, $flags, $options, $record, $argument_record, $is_set);
                $argument[$argument_nr] = $value;
            }
            $use_class = $object->config('package.raxon/parse.build.use.class');
            foreach($use_class as $use_as){
                $explode = explode('as', $use_as);
                if(array_key_exists(1, $explode)){
                    $use_class_name = trim($explode[1]);
                } else {
                    $explode = explode('\\', $use_as);
                    $use_class_name = array_pop($explode);
                }
                if($use_class_name === $class_name){
                    $class_name = $use_as;
                    break;
                }
            }
            $before[] = 'try {';
            $before[] = $uuid . ' = new ReflectionClass(\'' . $class_name . '\');';
            $before[] = $uuid_methods . ' = ' . $uuid . '->getMethods();';
            $before[] = 'foreach (' . $uuid_methods . ' as $nr => $method) {';
            $before[] = 'if ($method->isStatic()) {';
            $before[] = $uuid_methods . '[$nr] = $method->name;';
            $before[] = '} else {';
            $before[] = 'unset(' . $uuid_methods . '[$nr]);';
            $before[] = '}';
            $before[] = '}';
//            $before[] = 'd( ' . $uuid_methods . ');';
            $before[] = 'if(!in_array(\'' . $function . '\', ' . $uuid_methods. ', true)){';
            $before[] = 'sort(' . $uuid_methods .', SORT_NATURAL);';
            $before[] = 'throw new TemplateException(\'Static method "' . $function . '" not found in class: ' . $class_name . '\' . PHP_EOL . \'Available static methods:\' . PHP_EOL . implode(PHP_EOL, ' . $uuid_methods . ') . PHP_EOL);';
            $before[] = '}';
            $before[] = '}';
            $before[] = 'catch(Error | ErrorException | Exception | ParseError | LocateException | TemplateException $exception){';
            if(
                array_key_exists('is_multiline', $record) &&
                $record['is_multiline'] === true
            ){
                $before[] = 'throw new TemplateException(\'' . str_replace(['\\','\''], ['\\\\', '\\\''], $record['tag']) . PHP_EOL . 'On line: ' . $record['line']['start']  . ', column: ' . $record['column'][$record['line']['start']]['start'] . ' in source: ' . $source . '.\'  . (string) $exception, 0, $exception);';
            } else {
                $before[] = 'throw new TemplateException(\'' . str_replace(['\\','\''], ['\\\\', '\\\''], $record['tag']) . PHP_EOL . 'On line: ' . $record['line']  . ', column: ' . $record['column']['start'] . ' in source: ' . $source . '.\' . (string) $exception, 0, $exception);';
            }
            $before[] = '}';
            if(array_key_exists(0, $argument)){
                $value = $method . '(' . implode(', ', $argument) . ')';
            } else {
                $value = $method . '()';
            }
        }
        elseif(
            array_key_exists('value', $record['variable']) &&
            is_array($record['variable']['value']) &&
            array_key_exists('array', $record['variable']['value']) &&
            is_array($record['variable']['value']['array']) &&
            array_key_exists(0, $record['variable']['value']['array']) &&
            is_array($record['variable']['value']['array'][0]) &&
            array_key_exists('value', $record['variable']['value']['array'][0]) &&
            $record['variable']['value']['array'][0]['value'] === '$' &&
            array_key_exists(1, $record['variable']['value']['array']) &&
            is_array($record['variable']['value']['array'][1]) &&
            array_key_exists('method', $record['variable']['value']['array'][1]) &&
            array_key_exists('name', $record['variable']['value']['array'][1]['method'])
        ){
            //class method call
//            breakpoint($record);
            $method = $record['variable']['value']['array'][1]['method']['name'] ?? null;
            $explode = explode('.', $method, 2);
            //replace : with \\ for namespace in $explode[0]
            $class_raw = $explode[0];
            $class_name = str_replace(':', '\\', $class_raw);
            $class_object = '$' . $class_name;
            $class_method = str_replace('.', '_', $explode[1]);
            $uuid = Core::uuid_variable();
            $uuid_methods = Core::uuid_variable();
            $argument = $record['variable']['value']['array'][1]['method']['argument'];
            foreach($argument as $argument_nr => $argument_record){
                $value = Php::value($object, $flags, $options, $record, $argument_record, $is_set);
                $argument[$argument_nr] = $value;
            }
            $before[] = 'try {';
            $before[] = $uuid . ' = $data->data(\'' . $class_name . '\');';
            $before[] = $uuid_methods . ' = get_class_methods(' . $uuid . ');';
            $before[] = 'if(!in_array(\'' . $class_method . '\', ' . $uuid_methods. ', true)){';
            $before[] = 'sort(' . $uuid_methods .', SORT_NATURAL);';
            $before[] = 'throw new TemplateException(\'Method "' . $class_method . '" not found in class: ' . $class_raw . '\' . PHP_EOL . \'Available methods:\' . PHP_EOL . implode(PHP_EOL, ' . $uuid_methods . ') . PHP_EOL);';
            $before[] = '}';
            $before[] = '}';
            $before[] = 'catch(Error | ErrorException | Exception | ParseError | LocateException | TemplateException $exception){';
            if(
                array_key_exists('is_multiline', $record) &&
                $record['is_multiline'] === true
            ){
                $before[] = 'throw new TemplateException(\'' . str_replace(['\\','\''], ['\\\\', '\\\''], $record['tag']) . PHP_EOL . 'On line: ' . $record['line']['start']  . ', column: ' . $record['column'][$record['line']['start']]['start'] . ' in source: ' . $source . '.\' . PHP_EOL . (string) $exception, 0, $exception);';
            } else {
                $before[] = 'throw new TemplateException(\'' . str_replace(['\\','\''], ['\\\\', '\\\''], $record['tag'])  . PHP_EOL . 'On line: ' . $record['line']  . ', column: ' . $record['column']['start'] . ' in source: ' . $source . '.\' . PHP_EOL . (string) $exception, 0, $exception);';
            }
            $before[] = '}';
            if(array_key_exists(0, $argument)){
                $value = $uuid . '->' . $class_method .  '(' . implode(', ', $argument) . ')';
            } else {
                $value = $uuid . '->' . $class_method . '()';
            }
        }
        elseif(
            array_key_exists('value', $record['variable']) &&
            is_array($record['variable']['value']) &&
            array_key_exists('array', $record['variable']['value']) &&
            is_array($record['variable']['value']['array']) &&
            array_key_exists(0, $record['variable']['value']['array']) &&
            is_array($record['variable']['value']['array'][0]) &&
            array_key_exists('type', $record['variable']['value']['array'][0]) &&
            array_key_exists(1, $record['variable']['value']['array']) &&
            is_array($record['variable']['value']['array'][1]) &&
            array_key_exists('value', $record['variable']['value']['array'][1]) &&
            array_key_exists(2, $record['variable']['value']['array']) &&
            is_array($record['variable']['value']['array'][2]) &&
            array_key_exists('type', $record['variable']['value']['array'][2]) &&
            $record['variable']['value']['array'][0]['type'] === 'string' &&
            $record['variable']['value']['array'][1]['value'] === '::' &&
            $record['variable']['value']['array'][2]['type'] === 'method'
        ){
            //static method call
            $name = $record['variable']['value']['array'][0]['value'];
            $name .= $record['variable']['value']['array'][1]['value'];
            $class_static = Php::class_static($object);
            if(
                in_array(
                    $name,
                    $class_static,
                    true
                )
            ){
                $name .= $record['variable']['value']['array'][2]['method']['name'];
                $argument = $record['variable']['value']['array'][2]['method']['argument'];
                foreach($argument as $argument_nr => $argument_record){
                    $value = Php::value($object, $flags, $options, $record, $argument_record, $is_set, $before, $after);
                    $argument[$argument_nr] = $value;
                }
                if(array_key_exists(0, $argument)){
                    $value = $name . '(' . implode(', ', $argument) . ')';
                } else {
                    $value = $name . '()';
                }
            } else {
                if(
                    array_key_exists('is_multiline', $record) &&
                    $record['is_multiline'] === true
                ){
                    throw new TemplateException(
                        $record['tag'] . PHP_EOL .
                        'Unknown static class call "{{' . $name .'}}" please add the class usage on line: ' .
                        $record['line']['start']  .
                        ', column: ' .
                        $record['column'][$record['line']['start']]['start'] .
                        ' in source: '.
                        $source,
                    );

                } else {
                    throw new TemplateException(
                        $record['tag'] . PHP_EOL .
                        'Unknown static class call "{{' . $name .'}}" please add the class usage on line: ' .
                        $record['line'] .
                        ', column: ' .
                        $record['column']['start'] .
                        ' in source: '.
                        $source,
                    );
                }
            }
        } else {
            $value = Php::value($object, $flags, $options, $record, $record['variable']['value'],$is_set, $before, $after);
        }
        if(array_key_exists('modifier', $record['variable'])){
            d($value);
            ddd('what happens with value');
            $previous_modifier = '$data->data(\'' . $record['variable']['name'] . '\')';
            $modifier_value = '';
            foreach($record['variable']['modifier'] as $nr => $modifier){
                $plugin = Php::plugin($object, $flags, $options, $record, str_replace('.', '_', $modifier['name']));
                $modifier_value = $plugin . '(';
                $modifier_value .= $previous_modifier .', ';
                if(array_key_exists('argument', $modifier)){
                    $is_argument = false;
                    foreach($modifier['argument'] as $argument_nr => $argument){
                        $argument = Php::value($object, $flags, $options, $record, $argument, $is_set);
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
            if(array_key_exists('array_notation', $record['variable'])){
                $array_notation = Php::value($object, $flags, $options, $record, $record['variable']['array_notation'], $is_set, $before, $after);
                $variable_name .= $array_notation;
            }
            $result = $before;
            $result_validator = $before;
            if($value !== ''){
                if($try_catch !== false){
                    $separator = $object->config('package.raxon/parse.build.state.separator');
                    $result[] = 'try {';
                    foreach($before_value as $before_record){
                        $result[] = str_replace($separator, ';', $before_record);
                    }
                }
                switch($operator){
                    case '=' :
                        $item = '$data->set(' .
                            '\'' .
                            str_replace('\'', '\\\'', $variable_name) .
                            '\', ' .
                            $value .
                            ')'
                        ;
                        $item_validator = $item  . ';';
                        $result_validator[] = $item_validator;
                        if($separator !== null){
                            $item .= $separator;
                        } else {
                            $item .= ';';
                        }
                        $result[] = $item;
                        if($try_catch !== false){
                            foreach($after_value as $after_record){
                                if(!is_array($after_record)){
                                    $result[] = $after_record;
                                }
                            }
                            $result[] = '} catch(Error | ErrorException | Exception | ParseError | LocateException | TemplateException $exception){';
                            if(
                                array_key_exists('is_multiline', $record) &&
                                $record['is_multiline'] === true
                            ){
                                $result[] = 'throw new TemplateException(\'' . str_replace(['\\','\''], ['\\\\', '\\\''], $record['tag']) . PHP_EOL . 'On line: ' . $record['line']['start']  . ', column: ' . $record['column'][$record['line']['start']]['start'] . ' in source: '. $source . '\' . PHP_EOL . (string) $exception, 0, $exception);';
                            } else {
                                $result[] = 'throw new TemplateException(\'' . str_replace(['\\','\''], ['\\\\', '\\\''], $record['tag']) . PHP_EOL . 'On line: ' . $record['line']  . ', column: ' . $record['column']['start'] . ' in source: ' . $source . '\' . PHP_EOL . (string) $exception, 0, $exception);';
                            }
                            $result[] = '}';
                        }
                        break;
                    case '.=' :
                        $item = '$data->set(' .
                            '\'' .
                            str_replace('\'', '\\\'', $variable_name) .
                            '\', ' .
                            '$this->value_concatenate(' .
                            '$data->data(' .
                            '\'' .
                            str_replace('\'', '\\\'', $variable_name) .
                            '\'), ' .
                            $value .
                            ')' .
                            ')'
                        ;
                        $item_validator = $item  . ';';
                        $result_validator[] = $item_validator;
                        if($separator !== null){
                            $item .= $separator;
                        } else {
                            $item .= ';';
                        }
                        $result[] = $item;
                        if($try_catch !== false) {
                            foreach ($after_value as $after_record) {
                                if (!is_array($after_record)) {
                                    $result[] = $after_record;
                                }
                            }
                            $result[] = '} catch(Error | ErrorException | Exception | ParseError | LocateException | TemplateException $exception){';
                            if (
                                array_key_exists('is_multiline', $record) &&
                                $record['is_multiline'] === true
                            ) {
                                $result[] = 'throw new TemplateException(\'' . str_replace(['\\', '\''], ['\\\\', '\\\''], $record['tag']) . PHP_EOL . 'On line: ' . $record['line']['start'] . ', column: ' . $record['column'][$record['line']['start']]['start'] . ' in source: ' . $source . '\' . (string) $exception, 0, $exception);';
                            } else {
                                $result[] = 'throw new TemplateException(\'' . str_replace(['\\', '\''], ['\\\\', '\\\''], $record['tag']) . PHP_EOL . 'On line: ' . $record['line'] . ', column: ' . $record['column']['start'] . ' in source: ' . $source . '\' . (string) $exception, 0, $exception);';
                            }
                            $result[] = '}';
                        }
                        break;
                    case '+=' :
                        $item = '$data->set(' .
                            '\'' .
                            str_replace('\'', '\\\'', $variable_name) .
                            '\', ' .
                            '$this->value_plus('.
                            '$data->data('.
                            '\'' .
                            str_replace('\'', '\\\'', $variable_name) .
                            '\'), ' .
                            $value .
                            ')' .
                            ')'
                        ;
                        $item_validator = $item  . ';';
                        $result_validator[] = $item_validator;
                        if($separator !== null){
                            $item .= $separator;
                        } else {
                            $item .= ';';
                        }
                        $result[] = $item;
                        if($try_catch !== false){
                            foreach($after_value as $after_record){
                                if(!is_array($after_record)){
                                    $result[] = $after_record;
                                }
                            }
                            $result[] = '} catch(Error | ErrorException | Exception | ParseError | LocateException | TemplateException $exception){';
                            if(
                                array_key_exists('is_multiline', $record) &&
                                $record['is_multiline'] === true
                            ){
                                $result[] = 'throw new TemplateException(\'' . str_replace(['\\','\''], ['\\\\', '\\\''], $record['tag']) . PHP_EOL . 'On line: ' . $record['line']['start']  . ', column: ' . $record['column'][$record['line']['start']]['start'] . ' in source: '. $source . '\' . (string) $exception, 0, $exception);';
                            } else {
                                $result[] = 'throw new TemplateException(\'' . str_replace(['\\','\''], ['\\\\', '\\\''], $record['tag']) . PHP_EOL . 'On line: ' . $record['line']  . ', column: ' . $record['column']['start'] . ' in source: ' . $source . '\' . (string) $exception, 0, $exception);';
                            }
                            $result[] = '}';
                        }
                        break;
                    case '-=' :
                        $item = '$data->set('.
                            '\'' .
                            str_replace('\'', '\\\'', $variable_name) .
                            '\', ' .
                            '$this->value_minus('.
                            '$data->data('.
                            '\'' .
                            str_replace('\'', '\\\'', $variable_name) .
                            '\'), ' .
                            $value .
                            ')'.
                            ')'
                        ;
                        $item_validator = $item  . ';';
                        $result_validator[] = $item_validator;
                        if($separator !== null){
                            $item .= $separator;
                        } else {
                            $item .= ';';
                        }
                        $result[] = $item;
                        if($try_catch !== false){
                            foreach($after_value as $after_record){
                                if(!is_array($after_record)){
                                    $result[] = $after_record;
                                }
                            }
                            $result[] = '} catch(Error | ErrorException | Exception | ParseError | LocateException | TemplateException $exception){';
                            if(
                                array_key_exists('is_multiline', $record) &&
                                $record['is_multiline'] === true
                            ){
                                $result[] = 'throw new TemplateException(\'' . str_replace(['\\','\''], ['\\\\', '\\\''], $record['tag']) . PHP_EOL . 'On line: ' . $record['line']['start']  . ', column: ' . $record['column'][$record['line']['start']]['start'] . ' in source: '. $source . '\' . (string) $exception, 0, $exception);';
                            } else {
                                $result[] = 'throw new TemplateException(\'' . str_replace(['\\','\''], ['\\\\', '\\\''], $record['tag']) . PHP_EOL . 'On line: ' . $record['line']  . ', column: ' . $record['column']['start'] . ' in source: ' . $source . '\' . (string) $exception, 0, $exception);';
                            }
                            $result[] = '}';
                        }
                        break;
                    case '*=' :
                        $item = '$data->set('.
                            '\'' .
                            str_replace('\'', '\\\'', $variable_name) .
                            '\', ' .
                            '$this->value_multiply('.
                            '$data->data('.
                            '\'' .
                            str_replace('\'', '\\\'', $variable_name) .
                            '\'), ' .
                            $value .
                            ')'.
                            ')'
                        ;
                        $item_validator = $item  . ';';
                        $result_validator[] = $item_validator;
                        if($separator !== null){
                            $item .= $separator;
                        } else {
                            $item .= ';';
                        }
                        $result[] = $item;
                        if($try_catch !== false){
                            foreach($after_value as $after_record){
                                if(!is_array($after_record)){
                                    $result[] = $after_record;
                                }
                            }
                            $result[] = '} catch(Error | ErrorException | Exception | ParseError | LocateException | TemplateException $exception){';
                            $result_validator[] = '} catch(Error | ErrorException | Exception | ParseError | LocateException | TemplateException $exception){';
                            if(
                                array_key_exists('is_multiline', $record) &&
                                $record['is_multiline'] === true
                            ){
                                $result[] = 'throw new TemplateException(\'' . str_replace(['\\','\''], ['\\\\', '\\\''], $record['tag']) . PHP_EOL . 'On line: ' . $record['line']['start']  . ', column: ' . $record['column'][$record['line']['start']]['start'] . ' in source: '. $source . '\' . (string) $exception, 0, $exception);';
                                $result_validator[] = 'throw new TemplateException(\'' . str_replace(['\\','\''], ['\\\\', '\\\''], $record['tag']) . PHP_EOL . 'On line: ' . $record['line']['start']  . ', column: ' . $record['column'][$record['line']['start']]['start'] . ' in source: '. $source . '\' . (string) $exception, 0, $exception);';
                            } else {
                                $result[] = 'throw new TemplateException(\'' . str_replace(['\\','\''], ['\\\\', '\\\''], $record['tag']) . PHP_EOL . 'On line: ' . $record['line']  . ', column: ' . $record['column']['start'] . ' in source: ' . $source . '\' . (string) $exception, 0, $exception);';
                                $result_validator[] = 'throw new TemplateException(\'' . str_replace(['\\','\''], ['\\\\', '\\\''], $record['tag']) . PHP_EOL . 'On line: ' . $record['line']  . ', column: ' . $record['column']['start'] . ' in source: ' . $source . '\' . (string) $exception, 0, $exception);';
                            }
                            $result[] = '}';
                            $result_validator[] = '}';
                        }
                        break;
                }
                $result = implode(PHP_EOL, $result);
                $result_validator = implode(PHP_EOL, $result_validator);
            } else {
                switch($operator){
                    case '++' :
                        $item = '$data->set(\'' . $variable_name . '\', ' .  '$this->value_plus_plus($data->data(\'' . $variable_name . '\')))';
                        $result_validator = $item  . ';';
                        if($separator !== null){
                            $item .= $separator;
                        } else {
                            $item .= ';';
                        }
                        $result = $item;
                        break;
                    case '--' :
                        $item = '$data->set(\'' . $variable_name . '\', ' .  '$this->value_minus_minus($data->data(\'' . $variable_name . '\')))';
                        $result_validator = $item  . ';';
                        if($separator !== null){
                            $item .= $separator;
                        } else {
                            $item .= ';';
                        }
                        $result = $item;
                        break;
                    case '**' :
                        $item = '$data->set(\'' . $variable_name . '\', ' .  '$this->value_multiply_multiply($data->data(\'' . $variable_name . '\')))';
                        $result_validator = $item  . ';';
                        if($separator !== null){
                            $item .= $separator;
                        } else {
                            $item .= ';';
                        }
                        $result = $item;
                        break;
                }
            }
            try {
                $try_catch = $object->config('package.raxon/parse.build.state.try_catch');
                if($try_catch !== false){
                    Validator::validate($object, $flags, $options, $result_validator);
                }
            }
            catch(Exception $exception){
                if(
                    array_key_exists('is_multiline', $record) &&
                    $record['is_multiline'] === true
                ){
                    throw new TemplateException($record['tag'] .  PHP_EOL . 'On line: ' . $record['line']['start']  . ', column: ' . $record['column'][$record['line']['start']]['start'] . ' in source: '. $source . '.' . PHP_EOL . (string) $exception, 0, $exception);
                } else {
                    throw new TemplateException($record['tag'] . PHP_EOL . 'On line: ' . $record['line']  . ', column: ' . $record['column']['start'] . ' in source: ' . $source . '.' . PHP_EOL . (string) $exception, 0, $exception);
                }
            }
            $object->config('package.raxon/parse.build.state.remove_newline_next', true);
            return $result;
        }
        return false;
    }
}