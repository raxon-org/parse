{{$nested.counter = 0}}
{{for(; $nested.counter < 10; $nested.counter++)}}
    {{$nested.counter}}
{{/for}}
{{for(; $nested.counter < 12; $nested.counter++)}}
    {{$nested.counter}}
{{/for}}