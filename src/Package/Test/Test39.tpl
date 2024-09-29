{{$counter = 1}}
{{while(true)}}
    {{$counter}}
    {{$counter++}}
    {{if($counter > 10)}}
        {{break)}}
    {{/if}}
{{/while}}
/**
 * Error: needs to trigger error on line 6
 */