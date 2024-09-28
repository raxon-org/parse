{{$counter = 1}}
{{while($counter <= 10)}}
    {{$counter}}
    {{$counter = value.plus.plus($counter)}}
{{/while}}