<?php
namespace Raxon\Parse\Module;

use Exception;
use Raxon\App;

use Raxon\Exception\LocateException;

use Raxon\Parse\Build\Php;

class Build {

    /**
     * @throws Exception
     * @throws LocateException
     */
    public static function create(App $object, $flags, $options, $tags=[]): array
    {
        $source = $object->config('package.raxon/parse.build.state.source.list') ?? [];
        $source[] = $options->source ?? null;
        $object->config('package.raxon/parse.build.state.source.list', $source);
        $options->class = $options->class ?? Build::class_name($options->source);
        Php::document_default($object, $flags, $options);
        $tags = Php::document_tag_prepare($object, $flags, $options, $tags);
        $data = Php::document_tag($object, $flags, $options, $tags);
        $document = Php::document_header($object, $flags, $options);
        $document = Php::document_use($object, $flags, $options, $document, 'package.raxon/parse.build.use.class');
        $document[] = '';
        $document[] = 'class '. $options->class .' {';
        $document[] = '';
        $object->config('package.raxon/parse.build.state.indent', 1);
        //indent++
        $document = Php::document_use($object, $flags, $options, $document, 'package.raxon/parse.build.use.trait');
        $document[] = '';
        $document = Php::document_construct($object, $flags, $options, $document);
        $document[] = '';
//        d($data);
        $document = Php::document_run($object, $flags, $options, $document, $data);
        $document[] = '}';
        return $document;
    }

    public static function embed(App $object, $flags, $options, $tags=[]): array
    {
        $tags = Php::document_tag_prepare($object, $flags, $options, $tags);
        $embed = Php::document_tag($object, $flags, $options, $tags);
        return $embed;
    }


    public static function class_name($class=''): string
    {
        return ltrim(
            str_replace(
                [
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
                    '+',
                    '=',
                    '{',
                    '}',
                    '|',
                    ':',
                    '\'',
                    '"',
                    '<',
                    '>',
                    ',',
                    '?',
                    '/',
                    ';',
                    '.',
                    ' ',
                    '~',
                    '`',
                    '[',
                    ']',
                    '\\',
                ],
                '_',
                $class
            ),
            '_'
        );
    }
}