<?php

use Raxon\Exception\ObjectException;
use Raxon\Exception\TemplateException;
use Raxon\Parse\Module\Parse;
use Raxon\App;
use Raxon\Module\Data;
use Raxon\Module\File;


// Example test case

test(
    /**
     * @throws ObjectException
     * @throws TemplateException
     */
    'parse', function () {
    $app = App::instance();
    $data = new Data($app->data());
    $flags = App::flags($app);
    $options = App::options($app);
    $options->source = $app->config('framework.dir.vendor') . 'raxon/parse/Test/Template/Config.1.tpl';
    $parse = new Parse($app, $data, $flags , $options);
    $string = File::read($options->source) ?? '';
    d($string);
    $compile = $parse->compile($string);
    expect($compile)->toContain("Application");
    expect($compile)->toContain("/vendor/");
});
