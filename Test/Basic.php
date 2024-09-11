<?php

use Package\Raxon\Parse\Service\Parse;
use Raxon\App;


// Example test case

/**
 * @throws \Raxon\Exception\ObjectException
 *
 */
test('parse', function () {
    $app = App::instance();
    $parse = new Parse($app, $app->data(), $app->flags(), $app->options());

    $string = '{{config(\'project.dir.root\')}}';
    $compile = $parse->compile($string);
    expect($compile)->toContain("Application");
});
