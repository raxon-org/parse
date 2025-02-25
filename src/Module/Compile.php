<?php
namespace Raxon\Parse\Module;

use Raxon\App;

use Raxon\Module\Autoload;
use Raxon\Module\Core;
use Raxon\Module\File;

use Plugin;
use Exception;
use ReflectionClass;

use Raxon\Exception\LocateException;
use Raxon\Exception\TemplateException;

class Compile
{
    use Plugin\Format_code;
    use Plugin\Basic;

    public function __construct(App $object, $flags, $options)
    {
        $this->object($object);
        $this->parse_flags($flags);
        $this->parse_options($options);
    }

    /**
     * @throws Exception
     */
    public static function create(App $object, $flags, $options, $tags=[]): array
    {
        $options->class = $options->class ?? 'Main';
        Compile::document_default($object, $flags, $options);
//        $data = Compile::document_tag($object, $flags, $options, $tags);
        $data = [];
        $document = Compile::document_header($object, $flags, $options);
        $document = Compile::document_use($object, $flags, $options, $document, 'package.raxon/parse.build.use.class');
        $document[] = '';
        $document[] = 'class '. $options->class .' {';
        $document[] = '';
        $object->config('package.raxon/parse.build.state.indent', 1);
        //indent++
        $document = Compile::document_use($object, $flags, $options, $document, 'package.raxon/parse.build.use.trait');
        $document[] = '';
        $document = Compile::document_construct($object, $flags, $options, $document);
        $document[] = '';
//        d($data);
        $document = Compile::document_run($object, $flags, $options, $document, $data);
        $document[] = '}';
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
        $build = new Compile($object, $flags, $options);
        $indent = $object->config('package.raxon/parse.build.state.indent');
        $document = Compile::document_run_throw($object, $flags, $options, $document);
        $document[] = str_repeat(' ', $indent * 4) . 'public function run(): mixed';
        $document[] = str_repeat(' ', $indent * 4) . '{';
        $indent++;
        $document[] = str_repeat(' ', $indent * 4) . 'ob_start();';
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
        $document = Compile::format($build, $document, $data, $indent);
        $document[] = str_repeat(' ', $indent * 4) . 'if(ob_get_level() >= 1){';
        $indent++;
        $document[] = str_repeat(' ', $indent * 4) . 'return ob_get_clean();';
        $indent--;
        $document[] = str_repeat(' ', $indent * 4) . '}';
        $document[] = str_repeat(' ', $indent * 4) . 'return null;';
        $indent--;
        $document[] = str_repeat(' ', $indent * 4) . '}';
        return $document;
    }

    public static function format(Build $build, $document=[], $data=[], $indent=2): array
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
        $code = $build->format_code($data, $format_options);
        foreach($code as $nr => $line){
            $document[] = $line;
        }
        return $document;
    }

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
}