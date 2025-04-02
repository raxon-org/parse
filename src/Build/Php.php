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
use Raxon\Module\Core;
use Raxon\Module\File;

use Raxon\Parse\Module\Token;

use Raxon\Parse\Module\Validator;
use ReflectionClass;

class Php {

    use Format_code;

    /**
     * @throws Exception
     */
    public static function document_default(App $object, $flags, $options): void
    {
        $use_class = $object->config('package.raxon/parse.build.use.class');
        if(empty($use_class)){
            $use_class = [];
            $use_class[] = 'Raxon\App';
            $use_class[] = 'Raxon\Module\Data';
            $use_class[] = 'Package\Raxon\Parse\Service\Parse';
            $use_class[] = 'Plugin';
            $use_class[] = 'Exception';
            $use_class[] = 'Raxon\Exception\TemplateException';
            $use_class[] = 'Raxon\Exception\LocateException';
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
        $document[] = str_repeat(' ', $indent * 4) . 'throw new TemplateException(\'$parse is not an instance of Package\Raxon\Parse\Service\Parse\');';
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
        $document = Php::format($document, $data, $indent);
        $document[] = str_repeat(' ', $indent * 4) . 'return implode(\'\', $content);';
        $indent--;
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
        $data = [];
        $if_depth = 0;
        $if_length = 0;
        $elseif_count = 0;
        $else = false;
        $if_method = 'if';
        $content = [];
        $remove_newline_next = $object->config('package.raxon/parse.build.state.remove_newline_next');
        foreach ($tags as $row_nr => $list) {
            foreach ($list as $nr => &$record) {
                if(
                    array_key_exists('method', $record) &&
                    array_key_exists('name', $record['method'])
                ){
                    if($record['method']['name'] === 'if'){
                        $if_depth++;
                        if($if_depth === 1){
                            if(!array_key_exists($if_method, $content)){
                                $content[$if_method] = [];
                            }
                            if(!array_key_exists('statement', $content[$if_method])){
                                $content[$if_method]['statement'] = $record;
                                /*
                                if(!array_key_exists('content', $content[$if_method])){
                                    $content[$if_method]['content'] = [];
                                }
                                if(!array_key_exists($row_nr, $content[$if_method]['content'])){
                                    $content[$if_method]['content'][$row_nr] = [];
                                }
                                $content[$if_method]['content'][$row_nr][] = $record;
                                */
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
                        $if_depth === 1
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
                            /*
                            if(!array_key_exists('content', $content[$if_method][$elseif_count - 1])){
                                $content[$if_method][$elseif_count - 1]['content'] = [];
                            }
                            if(!array_key_exists($row_nr, $content[$if_method][$elseif_count - 1]['content'])){
                                $content[$if_method]['content'][$elseif_count - 1][$row_nr] = [];
                            }
                            $content[$if_method]['content'][$row_nr][] = $record;
                            */
                            continue;
                        }
                    }
                    $record['if_depth'] = $if_depth;
                }
                elseif(
                    array_key_exists('marker', $record) &&
                    array_key_exists('name', $record['marker'])
                ){
                    $record['if_depth'] = $if_depth;
                    if($record['marker']['name'] === 'else'){
                        if($if_depth === 1) {
                            $if_method = 'else';
                            continue;
                            /*
                            if (!array_key_exists($if_method, $content)) {
                                $content[$if_method] = [];
                            }
                            if (!array_key_exists('statement', $content[$if_method])) {
                                $content[$if_method]['statement'] = $record;
                                continue;
                            }
                            */
                        }
                    }
                    if(
                        $record['marker']['name'] === 'if' &&
                        array_key_exists('is_close', $record['marker']) &&
                        $record['marker']['is_close'] === true
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
                            $content = [];
                        } else {
                            //nothing for now...
                        }
                        $if_depth--;
                    }
                } else {
                    $record['if_depth'] = $if_depth;
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
                } else {
                    if(array_key_exists('text', $record)){
                        if($remove_newline_next === true){
                            $record = Php::remove_newline_next($object, $flags, $options, $record);
                            $object->config('delete', 'package.raxon/parse.build.state.remove_newline_next');
                        }
                        $text = Php::text($object, $flags, $options, $record);
                        $data[] = '$content[] =  \'' . str_replace(['\\','\''], ['\\\\', '\\\''], $text) . '\';';
                    }
                    elseif(
                        array_key_exists('variable', $record) &&
                        array_key_exists('is_assign', $record['variable']) &&
                        $record['variable']['is_assign'] === true
                    ){
                        $data[] = Php::variable_assign($object, $flags, $options, $record);
                        $next = $list[$nr + 1] ?? null;
                        if($next){
                            $list[$nr + 1] = Php::remove_newline_next($object, $flags, $options, $next);
                        }
                    }
                    elseif(
                        array_key_exists('method', $record)
                    ){
                        $method = Php::method($object, $flags, $options, $record, $before, $after);
                        if(!empty($before)){
                            foreach($before as $line){
                                $data[] = $line;
                            }
                            $before = [];
                        }
                        $uuid_variable = Core::uuid_variable();
                        $data[] = $uuid_variable . ' = ' . $method . ';';
                        $data[] = 'if(is_scalar('. $uuid_variable . ')){';
                        $data[] = '$content[] = ' .  $uuid_variable . ';';
                        $data[] = '} ';
                        $data[] = 'elseif(';
                        $data[] = '    is_array('. $uuid_variable .') || ';
                        $data[] = '    is_object(' .  $uuid_variable . ')';
                        $data[] = '){';
                        $data[] = 'return ' .  $uuid_variable  . ';';
                        $data[] = '}'; //null
                        if(!empty($after)){
                            foreach($after as $line){
                                $data[] = $line;
                            }
                            $after = [];
                        }
                    }
                    else {
                        d($content);
                        ddd($record);
                    }
                }
            }
        }
        $object->config('delete', 'package.raxon/parse.build.state.remove_newline_next');
        return $data;
    }


    /**
     * @throws Exception
     */
    /*
    public static function document_tag(App $object, $flags, $options, $tags = []): array
    {
        $data = [];
        $if_depth = 0;
        $if_length = 0;
        $elseif_count = 0;
        $else = false;
        $if_method = 'if';
        $content = [];
        d($tags);
        foreach($tags as $row_nr => $list) {
            foreach ($list as $nr => &$record) {
                if(array_key_exists('text', $record)){
                    if($if_depth === 0){
                        $text = Php::text($object, $flags, $options, $record);
                        $data[] = '$content[] =  \'' . str_replace(['\\','\''], ['\\\\', '\\\''], $text) . '\';';
                    }
                    elseif(!array_key_exists('if_depth', $record)) {
                        $record['if_depth'] = $if_depth;
                        if(
                            $if_method  === 'if' &&
                            !array_key_exists('content', $content[$if_method])
                        ){
                            $content[$if_method]['content'] = [];
                        }
                        if(
                            $if_method  === 'if' &&
                            !array_key_exists($row_nr, $content[$if_method]['content'])
                        ){
                            $content[$if_method]['content'][$row_nr] = [];
                        }
                        if(
                            $elseif_count > 0 &&
                            $else === false
                        ){
                            if(!array_key_exists($elseif_count - 1, $content[$if_method])){
                                $content[$if_method][$elseif_count - 1] = [];
                            }
                            if(!array_key_exists('content', $content[$if_method][$elseif_count - 1])){
                                $content[$if_method][$elseif_count - 1]['content'] = [];
                            }
                            if(!array_key_exists($row_nr, $content[$if_method][$elseif_count - 1]['content'])){
                                $content[$if_method][$elseif_count - 1]['content'][$row_nr] = [];
                            }
                            $record['if_depth']--;
                            $content[$if_method][$elseif_count - 1]['content'][$row_nr][] = $record;
                        }
                        elseif($else === true){
                            $if_method = 'else';
                            if(!array_key_exists($if_method, $content)){
                                $content[$if_method] = [];
                            }
                            if(!array_key_exists('content', $content[$if_method])){
                                $content[$if_method]['content'] = [];
                            }
                            if(!array_key_exists($row_nr, $content[$if_method]['content'])){
                                $content[$if_method]['content'][$row_nr] = [];
                            }
                            $record['if_depth']--;
                            $content[$if_method]['content'][$row_nr][] = $record;
                        } else {
                            $record['if_depth']--;
                            $content[$if_method]['content'][$row_nr][] = $record;
                        }

                    }
                }
                elseif(
                    array_key_exists('method', $record)
                ){
                    if(
                        array_key_exists('name', $record['method']) &&
                        in_array(
                            $record['method']['name'],
                            [
                                'elseif',
                                'else.if',
                                'else_if',
                                'if'
                            ],
                            true
                        ) &&
                        !array_key_exists('if_depth', $record)
                    ){
                        if($record['method']['name'] === 'if'){
                            $if_depth++;
                        } else {
                            $if_length++;
                        }
                        $record['if_depth'] = $if_depth;
                        if($if_depth === 1){
                            switch($record['method']['name']) {
                                case 'if':
                                    if (!array_key_exists($if_method, $content)){
                                        $content[$if_method] = [];
                                    }
                                    if(!array_key_exists('content', $content[$if_method])){
                                        $content[$if_method]['statement'] = $record;
                                        $content[$if_method]['content'] = [];
                                    }
                                    if(!array_key_exists($row_nr, $content[$if_method]['content'])){
                                        $content[$if_method]['content'][$row_nr] = [];
                                    }
                                    break;
                                case 'elseif':
                                case 'else.if':
                                case 'else_if':
                                    $if_method = 'elseif';
                                    $elseif_count++;
                                    if (!array_key_exists($if_method, $content)) {
                                        $content[$if_method] = [];
                                    }
                                    if(!array_key_exists($elseif_count - 1, $content[$if_method])){
                                        $content[$if_method][$elseif_count - 1] = [];
                                    }
                                    if(!array_key_exists('content', $content[$if_method][$elseif_count - 1])){
                                        $content[$if_method][$elseif_count - 1]['statement'] = $record;
                                        $content[$if_method][$elseif_count - 1]['content'] = [];
                                    }
                                    if(!array_key_exists($row_nr, $content[$if_method][$elseif_count - 1]['content'])){
                                        $content[$if_method][$elseif_count - 1]['content'][$row_nr] = [];
                                    }
                                    break;
                            }
                        } else {
                            if (!array_key_exists($if_method, $content)){
                                $content[$if_method] = [];
                            }
                            if(!array_key_exists('content', $content[$if_method])){
                                $content[$if_method]['content'] = [];
                            }
                            if(!array_key_exists($row_nr, $content[$if_method]['content'])){
                                $content[$if_method]['content'][$row_nr] = [];
                            }
                            if($elseif_count > 0){
                                if(!array_key_exists($elseif_count - 1, $content[$if_method])){
                                    $content[$if_method][$elseif_count - 1] = [];
                                }
                                if(!array_key_exists('content', $content[$if_method][$elseif_count - 1])){
                                    $content[$if_method][$elseif_count - 1]['content'] = [];
                                }
                                if(!array_key_exists($row_nr, $content[$if_method][$elseif_count - 1]['content'])){
                                    $content[$if_method][$elseif_count - 1]['content'][$row_nr] = [];
                                }
                                $record['if_depth']--;
                                $content[$if_method][$elseif_count - 1]['content'][$row_nr][] = $record;
                            } else {
                                $record['if_depth']--;
                                $content[$if_method]['content'][$row_nr][] = $record;
                            }
                        }
                    } else {
                        if($if_depth > 0){
                            if($elseif_count > 0){
                                if(!array_key_exists($elseif_count - 1, $content[$if_method])){
                                    $content[$if_method][$elseif_count - 1] = [];
                                }
                                if(!array_key_exists('content', $content[$if_method][$elseif_count - 1])){
                                    $content[$if_method][$elseif_count - 1]['content'] = [];
                                }
                                if(!array_key_exists($row_nr, $content[$if_method][$elseif_count - 1]['content'])){
                                    $content[$if_method][$elseif_count - 1]['content'][$row_nr] = [];
                                }
                                $record['if_depth']--;
                                $content[$if_method][$elseif_count - 1]['content'][$row_nr][] = $record;
                            } else {
                                $record['if_depth']--;
                                $content[$if_method]['content'][$row_nr][] = $record;
                            }
                        } else {
                            trace();
                            d($record);
                            $method = Php::method($object, $flags, $options, $record, $before, $after);
                            if($method) {
                                if (!empty($before)) {
                                    foreach ($before as $line) {
                                        $data[] = $line;
                                    }
                                    $before = [];
                                }
                                $uuid_variable = Core::uuid_variable();
                                $data[] = 'try {';
                                $data[] = $uuid_variable . ' = ' . $method . ';';
                                $data[] = 'if(is_scalar(' . $uuid_variable . ')){';
                                $data[] = '$content[] = '. $uuid_variable . ';';
                                $data[] = '}';
                                $data[] = 'elseif(is_array(' . $uuid_variable . ') || is_object(' . $uuid_variable . ')){';
                                $data[] = 'return $uuid_variable;';
                                $data[] = '}';
                                $data[] = '} catch (TemplateException | Exception $exception) {';
                                $data[] = 'throw $exception;';
                                $data[] = '}';
                                if(!empty($after)){
                                    foreach($after as $line){
                                        $data[] = $line;
                                    }
                                    $after = [];
                                }
                            }
                        }
                    }
                }
                elseif(
                    array_key_exists('marker', $record) &&
                    array_key_exists('name', $record['marker']) &&
                    $record['marker']['name'] === 'else' &&
                    !array_key_exists('if_depth', $record)
                ) {
                    $record['if_depth'] = $if_depth;
                    $else = true;
                }
                elseif(
                    array_key_exists('marker', $record) &&
                    array_key_exists('name', $record['marker']) &&
                    array_key_exists('is_close', $record['marker']) &&
                    $record['marker']['is_close'] === true &&
                    $record['marker']['name'] === 'if' &&
                    !array_key_exists('if_depth', $record)
                ) {
                    //close if tag
                    $record['if_depth'] = $if_depth;
                    $if_depth--;
                    d($content);
                    if($if_depth === 0){
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
                        $if_content = PHP::document_tag($object, $flags, $options, $content['if']['content']);
                        foreach($if_content as $line){
                            $if_data[] = $line;
                        }
                        $if_data[] = '}';
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
                                $if_content = Php::document_tag($object, $flags, $options, $elseif['content']);
                                foreach($if_content as $line){
                                    $if_data[] = $line;
                                }
                                $if_data[] = '}';
                            }
                        }
                        if(array_key_exists('else', $content)){
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
                        $content = [];
                    } else {
                        d($record);
                    }
                } else {
                    d($record);
                }
            }
        }
        return $data;
    }

    public static function remove_if_depth($tags=[]): array
    {
        foreach($tags as $row_nr => &$list){
            foreach($list as $nr => &$record) {
                if(array_key_exists('if_depth', $record)){
                    unset($record['if_depth']);
                }
            }
        }
        return $tags;
    }
    */

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
            //might need to remove // /* /**    **/  */
            return $record['text'];
        }
        return false;
    }

    /**
     * @throws Exception
     */
    public static function method(App $object, $flags, $options, $record = [], &$before=[], &$after=[]): bool | string
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
                        'else.if'
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
                } else {
                    $method_value = $record['method']['name']  . '(';
                }
                $method_value .= Php::argument($object, $flags, $options, $record, $before, $after);
                $method_value .= ')';
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
            if(
                in_array(
                    $method_match,
                    [
                        'default',
                        'object',
                        'echo',
                        'parse',
                        'break',
                        'continue',
                        'constant',
                        'require',
                        'unset'
                    ],
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
                        if($instance->class === 'Raxon\\Attribute\\Argument'){
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
                    $argument = Php::value($object, $flags, $options, $record, $argument, $is_set, $before, $after);
                    $uuid_variable = Core::uuid_variable();
                    $before[] = $uuid_variable . ' = ' . $argument . ';';
                    if($attributes !== false){
                        $use_trait = $object->config('package.raxon/parse.build.use.trait');
                        $trait = 'Plugin\\Validate';
                        if($attributes !== false && !in_array($trait, $use_trait, true)){
                            $use_trait[] = $trait;
                            $object->config('package.raxon/parse.build.use.trait', $use_trait);
                            $attributes_transfer =  Core::object($attributes, Core::TRANSFER);
                        }
                        $attributes_transfer =  Core::object($attributes, Core::TRANSFER);
                        $before[] = '$this->validate(' . $uuid_variable . ', \'argument\', Core::object(\'' . $attributes_transfer . '\', Core::FINALIZE), ' . $nr . ');';
                    }
                    $argument = $uuid_variable;
                    if(
                        array_key_exists($nr, $argument_is_reference) &&
                        $argument_is_reference[$nr] === true &&
                        array_key_exists('attribute', $after[$nr])
                    ){
                        $after[$nr] = '$data->set(\'' .  $after[$nr]['attribute'] . '\', ' . $uuid_variable . ');';
                    } else {
                        $after[$nr] = null;
                    }
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
        if(
            in_array(
                $name_lowercase,
                [
                    'default',
                    'object',
                    'echo',
                    'parse',
                    'break',
                    'continue',
                    'constant',
                    'require',
                    'unset'
                ],
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
            $trait_name = $explode[0];
            $trait_function = $explode[1];
            $use_plugin = $trait_function;
            if(!in_array($use_plugin, $use, true)){
                $use[] = '\\' . $use_package  . 'Trait' . '\\' . $trait_name ;
                $use_trait_function[count($use) - 1] = $use_plugin;
                $object->config('package.raxon/parse.build.use.trait', $use);
                $object->config('package.raxon/parse.build.use.trait_function', $use_trait_function);
                return '$this->' . $use_plugin;
            }
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
                            breakpoint($record);
                            breakpoint($locate_exception);
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
                            breakpoint($record);
                            breakpoint($locate_exception);
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
                $value .= $record['value'];
            }
            elseif(
                array_key_exists('type', $record) &&
                $record['type'] === 'variable'
            ){
                $uuid_variable = Core::uuid_variable();
                $before[] = $uuid_variable . ' = $data->get(\'' . $record['name'] . '\');';
                $value .= $uuid_variable;
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
                        'or'
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
                        $value .= ' ' . $record['value'] .  ' ';
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
                                $right = $uuid_variable;
                            }
                            elseif(
                                array_key_exists('type', $next) &&
                                in_array(
                                    $next['type'],
                                    [
                                        'integer',
                                        'float',
                                        'boolean'
                                    ],
                                    true
                                )
                            ){
                                $right = $next['execute'];
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
                $value .= Php::method($object, $flags, $options, $record, $before, $after);
            } else {
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
            $before[] = 'catch(Exception | LocateException $exception){';
            if(
                array_key_exists('is_multiline', $record) &&
                $record['is_multiline'] === true
            ){
                $before[] = 'throw new TemplateException(\'' . str_replace(['\\','\''], ['\\\\', '\\\''], $record['tag']) . PHP_EOL . 'On line: ' . $record['line']['start']  . ', column: ' . $record['column'][$record['line']['start']]['start'] . ' in source: ' . $source . '.\', 0, $exception);';
            } else {
                $before[] = 'throw new TemplateException(\'' . str_replace(['\\','\''], ['\\\\', '\\\''], $record['tag']) . PHP_EOL . 'On line: ' . $record['line']  . ', column: ' . $record['column']['start'] . ' in source: ' . $source . '.\', 0, $exception);';
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
            $before[] = 'catch(Exception | TemplateException $exception){';
            if(
                array_key_exists('is_multiline', $record) &&
                $record['is_multiline'] === true
            ){
                $before[] = 'throw new TemplateException(\'' . str_replace(['\\','\''], ['\\\\', '\\\''], $record['tag']) . PHP_EOL . 'On line: ' . $record['line']['start']  . ', column: ' . $record['column'][$record['line']['start']]['start'] . ' in source: ' . $source . '.\', 0, $exception);';
            } else {
                $before[] = 'throw new TemplateException(\'' . str_replace(['\\','\''], ['\\\\', '\\\''], $record['tag'])  . PHP_EOL . 'On line: ' . $record['line']  . ', column: ' . $record['column']['start'] . ' in source: ' . $source . '.\', 0, $exception);';
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
            $result = $before;
            if($value !== ''){
                $result[] = 'try {';
                foreach($before_value as $before_record){
                    $result[] = $before_record;
                }
                switch($operator){
                    case '=' :
                        $result[] = '$data->set(' .
                            '\'' .
                            $variable_name .
                            '\', ' .
                            $value .
                            ');'
                        ;
                        foreach($after_value as $after_record){
                            if(!is_array($after_record)){
                                $result[] = $after_record;
                            }
                        }
                        $result[] = '} catch(ErrorException | Error | Exception $exception){';
                        if(
                            array_key_exists('is_multiline', $record) &&
                            $record['is_multiline'] === true
                        ){
                            $result[] = 'throw new TemplateException(\'' . str_replace(['\\','\''], ['\\\\', '\\\''], $record['tag']) . PHP_EOL . 'On line: ' . $record['line']['start']  . ', column: ' . $record['column'][$record['line']['start']]['start'] . ' in source: '. $source . '\', 0, $exception);';
                        } else {
                            $result[] = 'throw new TemplateException(\'' . str_replace(['\\','\''], ['\\\\', '\\\''], $record['tag']) . PHP_EOL . 'On line: ' . $record['line']  . ', column: ' . $record['column']['start'] . ' in source: ' . $source . '\', 0, $exception);';
                        }
                        $result[] = '}';
                        break;
                    case '.=' :
                        $result[] = '$data->set(' .
                            '\'' .
                            $variable_name .
                            '\', ' .
                            '$this->value_concatenate(' .
                            '$data->data(' .
                            '\'' .
                            $variable_name .
                            '\'), ' .
                            $value .
                            ')' .
                            ');'
                        ;
                        foreach($after_value as $after_record){
                            if(!is_array($after_record)){
                                $result[] = $after_record;
                            }
                        }
                        $result[] = '} catch(ErrorException | Error | Exception $exception){';
                        if(
                            array_key_exists('is_multiline', $record) &&
                            $record['is_multiline'] === true
                        ){
                            $result[] = 'throw new TemplateException(\'' . str_replace(['\\','\''], ['\\\\', '\\\''], $record['tag']) . PHP_EOL . 'On line: ' . $record['line']['start']  . ', column: ' . $record['column'][$record['line']['start']]['start'] . ' in source: '. $source . '\', 0, $exception);';
                        } else {
                            $result[] = 'throw new TemplateException(\'' . str_replace(['\\','\''], ['\\\\', '\\\''], $record['tag']) . PHP_EOL . 'On line: ' . $record['line']  . ', column: ' . $record['column']['start'] . ' in source: ' . $source . '\', 0, $exception);';
                        }
                        $result[] = '}';
                        break;
                    case '+=' :
                        $result[] = '$data->set(' .
                            '\'' .
                            $variable_name .
                            '\', ' .
                            '$this->value_plus('.
                            '$data->data('.
                            '\'' .
                            $variable_name .
                            '\'), ' .
                            $value .
                            ')' .
                            ');'
                        ;
                        foreach($after_value as $after_record){
                            if(!is_array($after_record)){
                                $result[] = $after_record;
                            }
                        }
                        $result[] = '} catch(ErrorException | Error | Exception $exception){';
                        if(
                            array_key_exists('is_multiline', $record) &&
                            $record['is_multiline'] === true
                        ){
                            $result[] = 'throw new TemplateException(\'' . str_replace(['\\','\''], ['\\\\', '\\\''], $record['tag']) . PHP_EOL . 'On line: ' . $record['line']['start']  . ', column: ' . $record['column'][$record['line']['start']]['start'] . ' in source: '. $source . '\', 0, $exception);';
                        } else {
                            $result[] = 'throw new TemplateException(\'' . str_replace(['\\','\''], ['\\\\', '\\\''], $record['tag']) . PHP_EOL . 'On line: ' . $record['line']  . ', column: ' . $record['column']['start'] . ' in source: ' . $source . '\', 0, $exception);';
                        }
                        $result[] = '}';
                        break;
                    case '-=' :
                        $result[] = '$data->set('.
                            '\'' .
                            $variable_name .
                            '\', ' .
                            '$this->value_minus('.
                            '$data->data('.
                            '\'' .
                            $variable_name .
                            '\'), ' .
                            $value .
                            ')'.
                            ');'
                        ;
                        foreach($after_value as $after_record){
                            if(!is_array($after_record)){
                                $result[] = $after_record;
                            }
                        }
                        $result[] = '} catch(ErrorException | Error | Exception $exception){';
                        if(
                            array_key_exists('is_multiline', $record) &&
                            $record['is_multiline'] === true
                        ){
                            $result[] = 'if(ob_get_level() >= 1){';
                            $result[] = 'ob_get_clean();';
                            $result[] = '}';
                            $result[] = 'throw new TemplateException(\'' . str_replace(['\\','\''], ['\\\\', '\\\''], $record['tag']) . PHP_EOL . 'On line: ' . $record['line']['start']  . ', column: ' . $record['column'][$record['line']['start']]['start'] . ' in source: '. $source . '\', 0, $exception);';
                        } else {
                            $result[] = 'if(ob_get_level() >= 1){';
                            $result[] = 'ob_get_clean();';
                            $result[] = '}';
                            $result[] = 'throw new TemplateException(\'' . str_replace(['\\','\''], ['\\\\', '\\\''], $record['tag']) . PHP_EOL . 'On line: ' . $record['line']  . ', column: ' . $record['column']['start'] . ' in source: ' . $source . '\', 0, $exception);';
                        }
                        $result[] = '}';
                        break;
                    case '*=' :
                        $result[] = '$data->set('.
                            '\'' .
                            $variable_name .
                            '\', ' .
                            '$this->value_multiply('.
                            '$data->data('.
                            '\'' .
                            $variable_name .
                            '\'), ' .
                            $value .
                            ')'.
                            ');'
                        ;
                        foreach($after_value as $after_record){
                            if(!is_array($after_record)){
                                $result[] = $after_record;
                            }
                        }
                        $result[] = '} catch(ErrorException | Error | Exception $exception){';
                        if(
                            array_key_exists('is_multiline', $record) &&
                            $record['is_multiline'] === true
                        ){
                            $result[] = 'throw new TemplateException(\'' . str_replace(['\\','\''], ['\\\\', '\\\''], $record['tag']) . PHP_EOL . 'On line: ' . $record['line']['start']  . ', column: ' . $record['column'][$record['line']['start']]['start'] . ' in source: '. $source . '\', 0, $exception);';
                        } else {
                            $result[] = 'throw new TemplateException(\'' . str_replace(['\\','\''], ['\\\\', '\\\''], $record['tag']) . PHP_EOL . 'On line: ' . $record['line']  . ', column: ' . $record['column']['start'] . ' in source: ' . $source . '\', 0, $exception);';
                        }
                        $result[] = '}';
                        break;
                }
                $result = implode(PHP_EOL, $result);
            } else {
                switch($operator){
                    case '++' :
                        $result = '$data->set(\'' . $variable_name . '\', ' .  '$this->value_plus_plus($data->data(\'' . $variable_name . '\')));';
                        break;
                    case '--' :
                        $result = '$data->set(\'' . $variable_name . '\', ' .  '$this->value_minus_minus($data->data(\'' . $variable_name . '\')));';
                        break;
                    case '**' :
                        $result = '$data->set(\'' . $variable_name . '\', ' .  '$this->value_multiply_multiply($data->data(\'' . $variable_name . '\')));';
                        break;
                }
            }
            try {
                Validator::validate($object, $flags, $options, $result);
            }
            catch(Exception $exception){
                if(
                    array_key_exists('is_multiline', $record) &&
                    $record['is_multiline'] === true
                ){
                    throw new TemplateException($record['tag'] .  PHP_EOL . 'On line: ' . $record['line']['start']  . ', column: ' . $record['column'][$record['line']['start']]['start'] . ' in source: '. $source . '.', 0, $exception);
                } else {
                    throw new TemplateException($record['tag'] . PHP_EOL . 'On line: ' . $record['line']  . ', column: ' . $record['column']['start'] . ' in source: ' . $source . '.', 0, $exception);
                }
            }
            return $result;
        }
        return false;
    }
}