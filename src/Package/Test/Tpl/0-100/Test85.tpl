{{$framework = framework()}}
{{$app = ::Raxon::App::instance()}}
{{$time.start = $framework.config('time.start')}}
{{$time.instance = $app.config()}}
{{dd($time)}}