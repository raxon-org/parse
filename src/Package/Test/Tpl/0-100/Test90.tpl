{{$start = microtime()}}
{{$framework.test = framework()}}
{{$app = ::Raxon::App::instance()}}
{{$time.framework = ($app::config('time.start') - $framework.test::config('time.start')) * 1000 + ' ms'}}
{{$instance = App::instance()}}
{{$time.app = ($instance::config('time.start') - $app::config('time.start')) * 1000 + ' ms'}}
{{$time.instance = (microtime() - $instance::config('time.start')) * 1000 + ' ms'}}
{{$time.script.1 = (microtime() - $start) * 1000 + ' ms'}}
{{$time.script.2 = ((microtime() - $start) * 1000) - ((microtime() - $instance::config('time.start')) * 1000) - (($app::config('time.start') - $framework.test::config('time.start')) * 1000) + ' ms'}}
{{$time.total = (microtime() - $framework.test::config('time.start')) * 1000 + ' ms'}}
{{breakpoint($time)}}