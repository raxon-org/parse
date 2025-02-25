{{for($nested.counter = 0, $nested.status = true; $nested.counter < 10; $nested.counter++)}}
    {{$nested.counter}}
{{/for}}
{{for(; $nested.counter < 12; $nested.counter++)}}
    {{$nested.counter}}
{{/for}}