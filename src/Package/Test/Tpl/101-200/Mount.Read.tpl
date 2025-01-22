{{$flags = app.flags()}}
{{$options = app.options()}}
{{$response = mount.read($flags, $options)}}
{{d($response)}}
