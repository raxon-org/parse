{{$list = [
    0,
    1,
    2,
    3,
    4,
    5,
    6,
    7,
    8,
    9
]}}
{{for($i=0; $i < 4; $i++)}}
{{d($list[$i])}}
    {{echo($list[$i] . constant('PHP_EOL'))}}
{{/for}}
