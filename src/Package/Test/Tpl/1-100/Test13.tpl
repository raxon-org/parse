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
{{$list = (object) []}}
{{foreach($list as $nr)}}
    {{echo($nr . PHP_EOL)}}
{{/foreach}}