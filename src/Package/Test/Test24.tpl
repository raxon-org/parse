{{$counter = 1}}
{{while($counter <= 10)}}
    {{$counter}}
    {{value.plus($counter)}}
{{/while}}