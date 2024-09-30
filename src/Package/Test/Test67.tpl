{{$counter = 1}}
{{$elseif = '0'}}
{{d($elseif)}}
{{for(;;)}}
    {{$counter}}
    {{$counter++}}
    {{if($counter > 10)}}
        {{break()}}
    {{elseif($elseif)}}
        {{$counter}} under 11 #error
    {{elseif((string) $elseif)}}
        {{$counter}} under 11 #test
    {{elseif($elseif === '0')}}
        {{$counter}} under 11 #ok
    {{else}}
        {{$counter}} up 2
    {{/if}}
{{/for}}