{{$flags = app.flags()}}
{{$options = app.options()}}
{{$response = app.mount.read($flags, $options)}}
{{d($response)}}
