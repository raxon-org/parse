{{$counter = 1}}
{{while(true)}}
    {{$counter}}
    {{$counter++}}
    {{if($counter > 10)}}
        {{break()}}
    {{elseif(null)}}
        {{$counter}} onder de 10
    {{else}}
        {{$counter}} boven de 5
    {{/if}}
{{/while}}