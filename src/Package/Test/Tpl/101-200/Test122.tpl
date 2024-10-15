{{for($i = 0; $i < 5; $i++)}}
{{$continue_outer = false}}

{{for($j = 0; $j < 5; $j++)}}
{{if($j == 3)}}
{{$continue_outer = true}}
{{break()}} // Breaks inner loop
{{/if}}
{{echo("i: {$i}, j: {$j}\n")}}
{{/for}}

{{if($continue_outer)}}
{{continue()}} // Continue the outer loop
{{/if}}
{{/for}}
