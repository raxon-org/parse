{{$nested.counter2 = 1}}
{{for($nested.counter = $nested.counter2, $nested.status = [1, 2, 3]; $nested.counter < 10; $nested.counter++)}}
    {{$nested.counter}}
{{/for}}
{{for(; $nested.counter < 12; $nested.counter++)}}
    {{$nested.counter}}
{{/for}}