{{$list=[
1,
2,
3
]}}
{{for.each($list as $key => $value)}}
{{$key}} => {{$value}}

{{/for.each}}