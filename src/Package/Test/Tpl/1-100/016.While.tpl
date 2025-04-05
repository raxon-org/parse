{{$i = 0}}
{{while(true)}}
{{$i++}}
{{if($i > 5)}}
{{break(2)}}
{{/if}}
{{$i}}

{{/while}}