{{$start = microtime()}}
{{$framework.test = framework()}}
{{$app = ::Raxon::App::instance()}}
{{$time.framework = ($app::config('time.start') - $framework.test::config('time.start')) * 1000}}
{{$instance = App::instance()}}
{{$time.app = ($instance::config('time.start') - $app::config('time.start')) * 1000}}
{{$time.instance = (microtime() - $instance::config('time.start')) * 1000}}
{{$time.script.1 = (microtime() - $start) * 1000}}
{{$time.script.2 = ($time.script.1 - $time.instance - $time.app) + ' ms'}}
{{$time.total = (microtime() - $framework.test::config('time.start')) * 1000 + ' ms'}}
{{breakpoint($time)}}