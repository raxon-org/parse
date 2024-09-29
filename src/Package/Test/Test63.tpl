{{$counter = 1}}
{{$elseif = false}}
{{while(true)}}
    {{$counter}}
    {{$counter++}}
    {{if($counter > 10)}}
        {{break()}}
    {{elseif($elseif)}}
        {{$counter}} onder de 10
    {{else}}
        {{$counter}} boven de 5
    {{/if}}
{{/while}}