{{$counter = 1}}
{{while(true)}}
    {{$counter}}
    {{$counter++}}
    {{if($counter > 10)}}
        {{break()}}
    {{else)}}
        {{$counter}} onder de 10
    {{/if}}
{{/while}}