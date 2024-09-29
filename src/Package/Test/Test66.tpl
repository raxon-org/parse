{{$counter = 1}}
{{$elseif = '0'}}
{{d($elseif)}}
{{while(true)}}
    {{$counter}}
    {{$counter++}}
    {{if($counter > 10)}}
        {{break()}}
    {{elseif($elseif == '0')}}
        {{$counter}} onder de 10
    {{else}}
        {{$counter}} boven de 5
    {{/if}}
{{/while}}