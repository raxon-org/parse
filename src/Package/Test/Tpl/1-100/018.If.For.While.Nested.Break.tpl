{{if(true === true)}}
{{$j=0}}
{{for($i=0; $i <=10; $i++)}}
{{while(true)}}
{{$j++}}
{{$i}} {{$j}}

{{if($j > 50)}}
{{break(2)}}
{{/if}}
{{/while}}
{{/for}}
{{/if}}