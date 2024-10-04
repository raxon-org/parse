{{$framework = framework()}}
{{$app = ::Raxon::App::instance()}}
{{$time = (object) []}}
{{$time.start = $framework.config('time.start')}}
{{$time.instance = app.config('time.start')}}
{{dd($time)}}