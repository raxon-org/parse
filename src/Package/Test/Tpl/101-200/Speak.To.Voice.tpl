{{$options = app.options()}}
{{$speak = $options.speak}}
{{if($speak == '')}}
/**
$speak == '' must bet !is.empty()
**/
Please provide the option -speak with a value.
{{else}}
{{$node = app.speak($speak)}}
{{$node|object:'json'}}
{{/if}}
