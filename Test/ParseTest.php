<?php

use Package\Raxon\Parse\Service\Parse;
use Raxon\App;
use Raxon\Module\Data;


// Example test case

test('parse', function () {
    $app = App::instance();
    $data = new Data($app->data());
    $parse = new Parse($app, $data, App::flags($app), App::options($app));
    $string = '{{config(\'project.dir.vendor\')}}';
    $compile = $parse->compile($string);
    ob_start();
    expect($compile)->toContain("Application");
    expect($compile)->toContain("/vendor/");
});
