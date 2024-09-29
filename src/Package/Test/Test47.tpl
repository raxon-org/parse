{{$counter = 1}}
{{while(true)}}
    {{$counter}}
    {{$counter++}}
    {{if $counter > 10}}
        {{break()}}
    {{else}}
        {{$counter + ' is less than 10'}}
    {{/if}}
{{/while}}