{{$counter = 1}}
{{while(true)}}
    {{$counter}}
    {{$counter++}}
    {{if($counter > 10)}}
        {{break(2)}}
    {{/if}}
{{/while}}