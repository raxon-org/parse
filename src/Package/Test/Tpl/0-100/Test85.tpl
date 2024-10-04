{{$framework = framework()}}
{{$app = ::Raxon::App::instance()}}
{{$time.start = $framework.config('time.start')}}
{{$time.instance = $app.config('time.start')}}
{{$time.duration = $time.instance - value.set($time.start * 1000) + 'ms'}}
{{dd($time)}}