{{$array = [
1,
2,
3,
4,
5
]}}
{{$array = [
'test' => 1,
'default' => 2,
]}}



{{foreach($array as $value)}}
{{$value}}

{{/foreach}}
{{for.each($array as $value)}}
{{$value}}

{{/for.each}}
{{for_each($array as $value)}}
{{$value}}

{{/for_each}}