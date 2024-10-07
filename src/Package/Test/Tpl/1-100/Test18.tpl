{{foreach([
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
]] as $nr => $value)}}
    {{echo($nr + ' ' + $value + ' ' + PHP_EOL)}}
{{/foreach}}