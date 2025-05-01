{{$request.target = html.target.create('section', ['name'+'-test' => config('controller.name') + '-main'])}}
{{$request.target2 = html.target.create('section', ['name'+'-test' => config('controller.name') => '-main'])}}
{{d($request)}}