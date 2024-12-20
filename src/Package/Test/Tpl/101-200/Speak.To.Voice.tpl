{{$options = app.options()}}
{{$speak = $options.speak}}
{{if(is.empty($speak))}}
Please provide the option -speak with a value.
{{else}}
{{$node = app.speak($speak)}}
{{$node|json:constant('JSON_PRETTY_PRINT')}}
{{/if}}
