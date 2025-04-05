{{$i = 0}}
{{while(true)}}
{{$i++}}
{{if($i > 5)}}
{{break()}}
{{/if}}
{{$i}}

{{/while}}