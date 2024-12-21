{{$options = app.options()}}
{{$node = app.music.files($$options)}}
{{$node|object:'json'}}
