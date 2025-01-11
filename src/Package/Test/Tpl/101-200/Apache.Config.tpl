{{$options = app.options()}}
{{$node = apache.config.generation($$options)}}
{{$node|object:'json'}}