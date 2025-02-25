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

    public static function create(App $object, $flags, $options, $tags=[]): array
    {
        ddd($tags);
        $data = [];
        return $data;
    }
}