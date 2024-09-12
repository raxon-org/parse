<?php

use Package\Raxon\Parse\Service\Parse;
use Raxon\App;
use Raxon\Module\Data;


// Example test case

test('parse', function () {
    $app = App::instance();
    $data = new Data($app->data());
    $parse = new Parse($app, $data, App::flags($app), App::options($app));

    $init = $app->config('core.execute.stream.init');
    $app->config('core.execute.stream.init', true);

    $string = '{{config(\'project.dir.vendor\')}}';
    $compile = $parse->compile($string);

    ddd($compile);

    expect($compile)->toContain("Application");
});
