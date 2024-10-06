{{$framework.test = framework()}}
{{$app = ::Raxon::App::instance()}}
{{$time.framework = ($app::config('time.start') - $framework.test::config('time.start')) * 1000 + ' ms'}}
{{$instance = App::instance()}}
{{$time.app = ($instance::config('time.start') - $app::config('time.start')) * 1000 + ' ms'}}
{{$time.instance = (microtime() - $app::config('time.start')) * 1000 + ' ms'}}
{{breakpoint($time)}}