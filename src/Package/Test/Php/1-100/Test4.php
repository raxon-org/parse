<?php

$item['Node']['property'] = testfunction();

var_dump($item);

function testfunction(){
    echo 'Leave "name" empty if finished.' . PHP_EOL;
    fwrite(STDOUT, 'Leave "name" empty if finished.' . PHP_EOL);
    fflush(STDOUT);
    $input = trim(fgets(STDIN));
    echo 'Leave "name" empty if finished.' . PHP_EOL;
    $input = trim(fgets(STDIN));
    echo 'Leave "name" empty if finished.' . PHP_EOL;
    $input = trim(fgets(STDIN));
    return $input;
}

