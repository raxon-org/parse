<?php
$test = [
    'This',
    'is',
    'a',
    'test',
    'test'
];
asort($test, SORT_NATURAL);
foreach($test as $nr => $value) {
    $array[] = $value;
    $index[] = $nr;
}
var_dump($array);
var_dump($index);
