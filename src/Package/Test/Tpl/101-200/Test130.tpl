{{$counter = 1}}
{{$break = 1}}
{{while(true)}}
    {{$counter}}
    {{$counter++}}
    {{if($counter > 10)}}
        {{break((int) $break)}}
    {{/if}}
{{/while}}