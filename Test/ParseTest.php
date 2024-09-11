<?php

use Package\Raxon\Parse\Service\Parse;
use Raxon\App;
use Raxon\Module\Data;


// Example test case

test('parse', function () {
    $app = App::instance();
    $data = new Data($app);
    $parse = new Parse($app, $app->data(), App::flags($app), App::options($app));

    $string = '{{config(\'project.dir.root\')}}';
    $compile = $parse->compile($string);
    expect($compile)->toContain("Application");
});
